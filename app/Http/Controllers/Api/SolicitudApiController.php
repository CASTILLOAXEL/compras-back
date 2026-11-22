<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateSolicitudApiRequest;
use App\Http\Requests\Api\UpdateSolicitudApiRequest;
use App\Models\Solicitud;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de solicitudes. Cada acción exige su permiso en el token (abilities).
 */
class SolicitudApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver solicitudes', only: ['index', 'show']),
            new Middleware('abilities:crear solicitudes', only: ['store']),
            new Middleware('abilities:editar solicitudes', only: ['update']),
            new Middleware('abilities:eliminar solicitudes', only: ['destroy']),
        ];
    }

    /**
     * GET api/solicitudes?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $solicitudes = QueryBuilder::for(Solicitud::class)
            ->allowedFilters([
                'numero',
                AllowedFilter::exact('fecha'),
                AllowedFilter::exact('solicitante_id'),
                AllowedFilter::exact('centro_costo_id'),
                AllowedFilter::exact('estado_solicitud_id'),
            ])
            ->allowedSorts([
                'id',
                'numero',
                'fecha',
                'solicitante_id',
                'centro_costo_id',
                'estado_solicitud_id',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($solicitudes, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/solicitudes
     */
    public function store(CreateSolicitudApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $solicitud = Solicitud::create($request->validated());

            return $this->sendResponse($solicitud, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/solicitudes/{id}
     */
    public function show(Solicitud $solicitud): JsonResponse
    {
        return $this->sendResponse($solicitud, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/solicitudes/{id}
     */
    public function update(UpdateSolicitudApiRequest $request, Solicitud $solicitud): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $solicitud) {
            $solicitud->update($request->validated());

            return $this->sendResponse($solicitud, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/solicitudes/{id}  (soft delete)
     */
    public function destroy(Solicitud $solicitud): JsonResponse
    {
        return $this->executeOperation(function () use ($solicitud) {
            $solicitud->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
