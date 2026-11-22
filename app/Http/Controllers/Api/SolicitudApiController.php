<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateSolicitudApiRequest;
use App\Http\Requests\Api\UpdateSolicitudApiRequest;
use App\Models\EstadoSolicitud;
use App\Models\Solicitud;
use App\Models\Usuario;
use App\Services\SolicitudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * MODIFICADO: CRUD de solicitudes con su flujo de aprobación.
 * Las reglas del negocio viven en SolicitudService.
 */
class SolicitudApiController extends AppBaseController implements HasMiddleware
{
    private const RELACIONES_LISTADO = ['solicitante', 'centroCosto', 'estadoSolicitud'];

    private const RELACIONES_DETALLE = [
        ...self::RELACIONES_LISTADO,
        'detalles.producto',
        'detalles.unidadMedida',
        'historial.estadoSolicitud',
        'historial.usuario',
    ];

    public function __construct(private readonly SolicitudService $solicitudService)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver solicitudes', only: ['index', 'show']),
            new Middleware('abilities:crear solicitudes', only: ['store']),
            new Middleware('abilities:editar solicitudes', only: ['update']),
            new Middleware('abilities:eliminar solicitudes', only: ['destroy']),
            new Middleware('abilities:enviar solicitudes', only: ['enviar']),              // NUEVO
            new Middleware('abilities:aprobar solicitudes', only: ['aprobar', 'rechazar']), // NUEVO
        ];
    }


    public function index(Request $request): JsonResponse
    {
        $solicitudes = QueryBuilder::for(Solicitud::visiblesPara($request->user()))
            ->with(self::RELACIONES_LISTADO)
            ->allowedFilters([
                'numero',
                AllowedFilter::exact('centro_costo_id'),
                AllowedFilter::exact('estado_solicitud_id'),
                AllowedFilter::exact('solicitante_id'),
            ])
            ->allowedSorts(['id', 'numero', 'fecha', 'estado_solicitud_id'])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($solicitudes, 'Solicitudes recuperadas con éxito.');
    }

    public function store(CreateSolicitudApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $solicitud = $this->solicitudService->crear($request->validated('centro_costo_id'), $request->user());

            return $this->respuestaConDetalle($solicitud, 'Solicitud creada en borrador.');
        });
    }


    public function show(Request $request, Solicitud $solicitud): JsonResponse
    {
        $this->verificarAcceso($solicitud, $request->user());

        return $this->respuestaConDetalle($solicitud, 'Solicitud recuperada con éxito.');
    }
    public function update(UpdateSolicitudApiRequest $request, Solicitud $solicitud): JsonResponse
    {
        $this->verificarAcceso($solicitud, $request->user());

        return $this->executeTransaction(function () use ($request, $solicitud) {
            $this->solicitudService->validarEstado($solicitud, EstadoSolicitud::BORRADOR, 'Solo se pueden editar solicitudes en borrador.');

            $solicitud->update($request->validated());

            return $this->respuestaConDetalle($solicitud, 'Solicitud actualizada con éxito.');
        });
    }

    public function destroy(Request $request, Solicitud $solicitud): JsonResponse
    {
        $this->verificarAcceso($solicitud, $request->user());

        return $this->executeTransaction(function () use ($solicitud) {
            $this->solicitudService->validarEstado($solicitud, EstadoSolicitud::BORRADOR, 'Solo se pueden eliminar solicitudes en borrador.');

            $solicitud->delete();

            return $this->sendSuccess('Solicitud eliminada con éxito.');
        });
    }

    public function enviar(Request $request, Solicitud $solicitud): JsonResponse
    {
        $this->verificarAcceso($solicitud, $request->user());

        return $this->executeTransaction(function () use ($request, $solicitud) {
            $this->solicitudService->enviarAAprobacion($solicitud, $request->user());

            return $this->respuestaConDetalle($solicitud, 'Solicitud enviada a aprobación.');
        });
    }

    public function aprobar(Request $request, Solicitud $solicitud): JsonResponse
    {
        $datosValidados = $request->validate(['observacion' => ['nullable', 'string', 'max:500']]);

        return $this->executeTransaction(function () use ($request, $solicitud, $datosValidados) {
            $this->solicitudService->aprobar($solicitud, $request->user(), $datosValidados['observacion'] ?? null);

            return $this->respuestaConDetalle($solicitud, 'Solicitud aprobada.');
        });
    }

    public function rechazar(Request $request, Solicitud $solicitud): JsonResponse
    {
        $datosValidados = $request->validate(
            ['observacion' => ['required', 'string', 'max:500']],
            ['observacion.required' => 'Indique el motivo del rechazo.']
        );

        return $this->executeTransaction(function () use ($request, $solicitud, $datosValidados) {
            $this->solicitudService->rechazar($solicitud, $request->user(), $datosValidados['observacion']);

            return $this->respuestaConDetalle($solicitud, 'Solicitud rechazada.');
        });
    }

    private function verificarAcceso(Solicitud $solicitud, Usuario $usuario): void
    {
        $tieneAcceso = Solicitud::visiblesPara($usuario)->whereKey($solicitud->id)->exists();

        abort_unless($tieneAcceso, 403, 'No tiene acceso a esta solicitud.');
    }

    private function respuestaConDetalle(Solicitud $solicitud, string $mensaje): JsonResponse
    {
        $solicitud->load(self::RELACIONES_DETALLE)->append('total_estimado');

        return $this->sendResponse($solicitud, $mensaje);
    }
}
