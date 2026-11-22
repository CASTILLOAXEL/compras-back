<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\ReporteOrdenesApiRequest;
use App\Models\Contrato;
use App\Models\EstadoContrato;
use App\Models\EstadoOrden;
use App\Models\EstadoSolicitud;
use App\Models\Orden;
use App\Models\Proveedor;
use App\Models\Solicitud;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

/**
 * Reporte de órdenes por proveedor y estado, e indicadores de la pantalla de inicio.
 */
class ReporteApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver ordenes', only: ['ordenesPorProveedorYEstado']),
        ];
    }

    /**
     * GET /reportes/ordenes?proveedor_id=&estado_orden_id=&fecha_desde=&fecha_hasta=
     */
    public function ordenesPorProveedorYEstado(ReporteOrdenesApiRequest $request): JsonResponse
    {
        $filtros = $request->validated();

        // Resumen: una fila por proveedor y estado, con cantidad de órdenes y monto
        $resumen = $this->consultaOrdenesFiltradas($filtros)
            ->groupBy('proveedores.id', 'proveedores.razon_social', 'estados_orden.id', 'estados_orden.nombre')
            ->orderBy('proveedores.razon_social')
            ->orderBy('estados_orden.id')
            ->selectRaw('proveedores.id AS proveedor_id, proveedores.razon_social, estados_orden.id AS estado_orden_id, estados_orden.nombre AS estado')
            ->selectRaw('COUNT(ordenes.id) AS cantidad_ordenes, COALESCE(SUM(totales.total), 0) AS monto_total')
            ->get()
            ->map(fn (object $fila) => [
                'proveedor_id' => (int) $fila->proveedor_id,
                'razon_social' => $fila->razon_social,
                'estado_orden_id' => (int) $fila->estado_orden_id,
                'estado' => $fila->estado,
                'cantidad_ordenes' => (int) $fila->cantidad_ordenes,
                'monto_total' => round((float) $fila->monto_total, 2),
            ]);

        // Detalle: cada orden con su total
        $ordenes = $this->consultaOrdenesFiltradas($filtros)
            ->orderByDesc('ordenes.fecha_emision')
            ->orderByDesc('ordenes.id')
            ->select(['ordenes.id', 'ordenes.numero', 'ordenes.fecha_emision', 'ordenes.estado_orden_id', 'estados_orden.nombre AS estado', 'proveedores.razon_social'])
            ->selectRaw('COALESCE(totales.total, 0) AS total')
            ->get()
            ->map(fn (object $fila) => [
                'id' => (int) $fila->id,
                'numero' => $fila->numero,
                'fecha_emision' => substr((string) $fila->fecha_emision, 0, 10),
                'estado_orden_id' => (int) $fila->estado_orden_id,
                'estado' => $fila->estado,
                'razon_social' => $fila->razon_social,
                'total' => round((float) $fila->total, 2),
            ]);

        return $this->sendResponse([
            'resumen' => $resumen,
            'ordenes' => $ordenes,
            'totales' => [
                'cantidad_ordenes' => $ordenes->count(),
                'monto_total' => round($ordenes->sum('total'), 2),
            ],
        ], 'Reporte generado con éxito.');
    }

    /**
     * GET /inicio/indicadores
     * El solicitante solo cuenta sus propias solicitudes.
     */
    public function indicadoresInicio(Request $request): JsonResponse
    {
        $cantidadPorEstado = Solicitud::visiblesPara($request->user())
            ->selectRaw('estado_solicitud_id, COUNT(*) AS cantidad')
            ->groupBy('estado_solicitud_id')
            ->pluck('cantidad', 'estado_solicitud_id');

        $solicitudesPorEstado = EstadoSolicitud::orderBy('id')->get(['id', 'nombre'])
            ->map(fn (EstadoSolicitud $estadoSolicitud) => [
                'estado_solicitud_id' => $estadoSolicitud->id,
                'nombre' => $estadoSolicitud->nombre,
                'cantidad' => (int) ($cantidadPorEstado[$estadoSolicitud->id] ?? 0),
            ]);

        // Monto comprometido: órdenes no canceladas
        $montoOrdenes = DB::table('orden_detalles')
            ->join('ordenes', 'ordenes.id', '=', 'orden_detalles.orden_id')
            ->whereNull('orden_detalles.deleted_at')
            ->whereNull('ordenes.deleted_at')
            ->where('ordenes.estado_orden_id', '!=', EstadoOrden::CANCELADA)
            ->sum(DB::raw('orden_detalles.cantidad * orden_detalles.precio_unitario'));

        return $this->sendResponse([
            'solicitudes_por_estado' => $solicitudesPorEstado,
            'ordenes_emitidas' => Orden::where('estado_orden_id', EstadoOrden::EMITIDA)->count(),
            'monto_ordenes' => round((float) $montoOrdenes, 2),
            'contratos_vigentes' => Contrato::where('estado_contrato_id', EstadoContrato::VIGENTE)->count(),
            'proveedores_activos' => Proveedor::where('estado', true)->count(),
        ], 'Indicadores recuperados con éxito.');
    }

    /**
     * Órdenes con su proveedor, estado y total (subconsulta), aplicando los filtros.
     */
    private function consultaOrdenesFiltradas(array $filtros): Builder
    {
        $totalesPorOrden = DB::table('orden_detalles')
            ->whereNull('deleted_at')
            ->select('orden_id')
            ->selectRaw('SUM(cantidad * precio_unitario) AS total')
            ->groupBy('orden_id');

        return DB::table('ordenes')
            ->join('proveedores', 'proveedores.id', '=', 'ordenes.proveedor_id')
            ->join('estados_orden', 'estados_orden.id', '=', 'ordenes.estado_orden_id')
            ->leftJoinSub($totalesPorOrden, 'totales', 'totales.orden_id', '=', 'ordenes.id')
            ->whereNull('ordenes.deleted_at')
            ->when($filtros['proveedor_id'] ?? null, fn (Builder $consulta, int $proveedorId) => $consulta->where('ordenes.proveedor_id', $proveedorId))
            ->when($filtros['estado_orden_id'] ?? null, fn (Builder $consulta, int $estadoOrdenId) => $consulta->where('ordenes.estado_orden_id', $estadoOrdenId))
            ->when($filtros['fecha_desde'] ?? null, fn (Builder $consulta, string $fechaDesde) => $consulta->whereDate('ordenes.fecha_emision', '>=', $fechaDesde))
            ->when($filtros['fecha_hasta'] ?? null, fn (Builder $consulta, string $fechaHasta) => $consulta->whereDate('ordenes.fecha_emision', '<=', $fechaHasta));
    }
}
