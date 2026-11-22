<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\GenerarOrdenApiRequest;
use App\Http\Requests\Api\RegistrarRecepcionApiRequest;
use App\Models\Orden;
use App\Models\Solicitud;
use App\Services\Services\OrdenService;
use App\Services\Services\RecepcionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Órdenes de compra: se generan desde una solicitud aprobada; no se editan ni eliminan, solo se cancelan.
 */
class OrdenApiController extends AppBaseController implements HasMiddleware
{
    private const RELACIONES_LISTADO = ['proveedor', 'estadoOrden', 'solicitud', 'detalles'];

    private const RELACIONES_DETALLE = [
        'proveedor',
        'estadoOrden',
        'solicitud.centroCosto',
        'solicitud.solicitante',
        'detalles.solicitudDetalle.producto',
        'detalles.solicitudDetalle.unidadMedida',
        'detalles.recepcionDetalles',
        'recepciones.responsable',
        'recepciones.detalles.ordenDetalle.solicitudDetalle.producto',
    ];

    public function __construct(
        private readonly OrdenService $ordenService,
        private readonly RecepcionService $recepcionService, // NUEVO
    ) {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver ordenes', only: ['index', 'show']),
            new Middleware('abilities:generar ordenes', only: ['generarDesdeSolicitud']),
            new Middleware('abilities:cancelar ordenes', only: ['cancelar']),
            new Middleware('abilities:recibir ordenes', only: ['registrarRecepcion']), // NUEVO
        ];
    }

    public function index(): JsonResponse
    {
        $ordenes = QueryBuilder::for(Orden::class)
            ->with(self::RELACIONES_LISTADO)
            ->allowedFilters(...[
                'numero',
                AllowedFilter::exact('proveedor_id'),
                AllowedFilter::exact('estado_orden_id'),
                AllowedFilter::exact('solicitud_id'),
            ])
            ->allowedSorts(...['id', 'numero', 'fecha_emision', 'estado_orden_id'])
            ->defaultSort('-id')
            ->jsonPaginate();

        // El total se calcula con los ítems; luego se ocultan para no enviar de más en el listado
        $ordenes->getCollection()->each(fn (Orden $orden) => $orden->append('total')->makeHidden('detalles'));

        return $this->sendResponse($ordenes, 'Órdenes de compra recuperadas con éxito.');
    }

    public function show(Orden $orden): JsonResponse
    {
        return $this->respuestaConDetalle($orden, 'Orden de compra recuperada con éxito.');
    }

    /**
     * POST /solicitudes/{solicitud}/orden
     */
    public function generarDesdeSolicitud(GenerarOrdenApiRequest $request, Solicitud $solicitud): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $solicitud) {
            $orden = $this->ordenService->generarDesdeSolicitud(
                $solicitud,
                $request->integer('proveedor_id'),
                $request->validated('items'),
                $request->user(),
            );

            return $this->respuestaConDetalle($orden, "Orden de compra {$orden->numero} generada.");
        });
    }

    /**
     * POST /ordenes/{orden}/cancelar
     */
    public function cancelar(Orden $orden): JsonResponse
    {
        return $this->executeTransaction(function () use ($orden) {
            $this->ordenService->cancelar($orden);

            return $this->respuestaConDetalle($orden, 'Orden de compra cancelada.');
        });
    }

    /**

     * El responsable es el usuario que registra la recepción.
     */
    public function registrarRecepcion(RegistrarRecepcionApiRequest $request, Orden $orden): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $orden) {
            $this->recepcionService->registrar($orden, $request->validated(), $request->user());

            return $this->respuestaConDetalle($orden, 'Recepción registrada con éxito.');
        });
    }

    private function respuestaConDetalle(Orden $orden, string $mensaje): JsonResponse
    {
        $orden->load(self::RELACIONES_DETALLE)->append('total');

        // Datos calculados de cada ítem: subtotal y avance de la recepción
        $orden->detalles->each->append(['subtotal', 'cantidad_recibida', 'cantidad_pendiente', 'estado_recepcion']);

        return $this->sendResponse($orden, $mensaje);
    }
}
