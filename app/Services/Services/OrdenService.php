<?php

namespace App\Services\Services;

use App\Models\EstadoOrden;
use App\Models\EstadoSolicitud;
use App\Models\Orden;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Validation\ValidationException;

/**
 * Reglas del negocio de la orden de compra.
 */
class OrdenService
{
    public function __construct(private readonly SolicitudService $solicitudService)
    {
    }

    /**
     * Genera la orden de una solicitud aprobada y deja la solicitud en Completada.
     *
     * @param  array<int, array{solicitud_detalle_id: int, cantidad: float, precio_unitario: float}>  $items
     */
    public function generarDesdeSolicitud(Solicitud $solicitud, int $proveedorId, array $items, Usuario $usuario): Orden
    {
        $this->solicitudService->validarEstado(
            $solicitud,
            EstadoSolicitud::APROBADA,
            'Solo una solicitud aprobada puede generar una orden de compra.'
        );

        $this->validarItems($solicitud, $items);

        $orden = Orden::create([
            'numero' => $this->siguienteNumero(),
            'fecha_emision' => now()->toDateString(),
            'solicitud_id' => $solicitud->id,
            'proveedor_id' => $proveedorId,
            'estado_orden_id' => EstadoOrden::EMITIDA,
        ]);

        foreach ($items as $item) {
            $orden->detalles()->create([
                'solicitud_id' => $solicitud->id,
                'solicitud_detalle_id' => $item['solicitud_detalle_id'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
            ]);
        }

        $this->solicitudService->completar($solicitud, $usuario, "Generó la orden de compra {$orden->numero}.");

        return $orden;
    }

    /**
     * Solo se cancela una orden emitida que todavía no tiene recepciones.
     */
    public function cancelar(Orden $orden): Orden
    {
        if (! $orden->estaEnEstado(EstadoOrden::EMITIDA)) {
            throw ValidationException::withMessages(['orden' => 'Solo se puede cancelar una orden emitida.']);
        }

        if ($orden->recepciones()->exists()) {
            throw ValidationException::withMessages(['orden' => 'No se puede cancelar una orden que ya tiene recepciones.']);
        }

        $orden->update(['estado_orden_id' => EstadoOrden::CANCELADA]);

        return $orden;
    }

    /**
     * Cada ítem debe ser de la solicitud y no pedir más de lo solicitado.
     */
    private function validarItems(Solicitud $solicitud, array $items): void
    {
        $detallesSolicitud = $solicitud->detalles()->get()->keyBy('id');

        foreach ($items as $posicion => $item) {
            $solicitudDetalle = $detallesSolicitud->get($item['solicitud_detalle_id']);

            if (! $solicitudDetalle) {
                throw ValidationException::withMessages([
                    "items.{$posicion}.solicitud_detalle_id" => 'El ítem no pertenece a la solicitud.',
                ]);
            }

            if ((float) $item['cantidad'] > (float) $solicitudDetalle->cantidad) {
                throw ValidationException::withMessages([
                    "items.{$posicion}.cantidad" => "La cantidad no puede ser mayor a la solicitada ({$solicitudDetalle->cantidad}).",
                ]);
            }
        }
    }

    /**
     * OC-000001: bloquea la tabla dentro de la transacción para que no se repita el número.
     */
    private function siguienteNumero(): string
    {
        $ultimoId = Orden::withTrashed()->lockForUpdate()->max('id') ?? 0;

        return 'OC-'.str_pad((string) ($ultimoId + 1), 6, '0', STR_PAD_LEFT);
    }
}
