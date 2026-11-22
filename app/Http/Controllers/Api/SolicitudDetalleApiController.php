<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateSolicitudDetalleApiRequest;
use App\Http\Requests\Api\UpdateSolicitudDetalleApiRequest;
use App\Models\EstadoSolicitud;
use App\Models\Solicitud;
use App\Models\SolicitudDetalle;
use App\Models\Usuario;
use App\Services\Services\SolicitudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SolicitudDetalleApiController extends AppBaseController implements HasMiddleware
{
    private const RELACIONES = ['producto', 'unidadMedida'];

    public function __construct(private readonly SolicitudService $solicitudService)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver solicitud detalles', only: ['index', 'show']),
            new Middleware('abilities:crear solicitud detalles', only: ['store']),
            new Middleware('abilities:editar solicitud detalles', only: ['update']),
            new Middleware('abilities:eliminar solicitud detalles', only: ['destroy']),
        ];
    }


    public function index(Request $request): JsonResponse
    {
        $detalles = QueryBuilder::for(SolicitudDetalle::class)
            ->with(self::RELACIONES)
            ->whereIn('solicitud_id', Solicitud::visiblesPara($request->user())->select('id'))
            ->allowedFilters(...[
                AllowedFilter::exact('solicitud_id'),
                AllowedFilter::exact('producto_id'),
            ])
            ->allowedSorts(...['id', 'cantidad', 'precio_estimado'])
            ->defaultSort('id')
            ->jsonPaginate();

        $detalles->getCollection()->each->append('subtotal');

        return $this->sendResponse($detalles, 'Ítems recuperados con éxito.');
    }


    public function store(CreateSolicitudDetalleApiRequest $request): JsonResponse
    {
        $solicitud = Solicitud::findOrFail($request->validated('solicitud_id'));
        $this->verificarSolicitudEditable($solicitud, $request->user());

        return $this->executeTransaction(function () use ($request) {
            $detalle = SolicitudDetalle::create($request->validated());

            return $this->respuestaConRelaciones($detalle, 'Ítem agregado a la solicitud.');
        });
    }


    public function show(Request $request, SolicitudDetalle $solicitudDetalle): JsonResponse
    {
        $this->verificarAcceso($solicitudDetalle->solicitud, $request->user());

        return $this->respuestaConRelaciones($solicitudDetalle, 'Ítem recuperado con éxito.');
    }


    public function update(UpdateSolicitudDetalleApiRequest $request, SolicitudDetalle $solicitudDetalle): JsonResponse
    {
        $this->verificarSolicitudEditable($solicitudDetalle->solicitud, $request->user());

        return $this->executeTransaction(function () use ($request, $solicitudDetalle) {
            $solicitudDetalle->update($request->validated());

            return $this->respuestaConRelaciones($solicitudDetalle, 'Ítem actualizado con éxito.');
        });
    }


    public function destroy(Request $request, SolicitudDetalle $solicitudDetalle): JsonResponse
    {
        $this->verificarSolicitudEditable($solicitudDetalle->solicitud, $request->user());

        return $this->executeOperation(function () use ($solicitudDetalle) {
            $solicitudDetalle->delete();

            return $this->sendSuccess('Ítem quitado de la solicitud.');
        });
    }


    private function verificarAcceso(Solicitud $solicitud, Usuario $usuario): void
    {
        $tieneAcceso = Solicitud::visiblesPara($usuario)->whereKey($solicitud->id)->exists();

        abort_unless($tieneAcceso, 403, 'No tiene acceso a esta solicitud.');
    }


    private function verificarSolicitudEditable(Solicitud $solicitud, Usuario $usuario): void
    {
        $this->verificarAcceso($solicitud, $usuario);

        $this->solicitudService->validarEstado(
            $solicitud,
            EstadoSolicitud::BORRADOR,
            'Solo se pueden modificar los ítems de solicitudes en borrador.'
        );
    }

    private function respuestaConRelaciones(SolicitudDetalle $detalle, string $mensaje): JsonResponse
    {
        $detalle->load(self::RELACIONES)->append('subtotal');

        return $this->sendResponse($detalle, $mensaje);
    }
}
