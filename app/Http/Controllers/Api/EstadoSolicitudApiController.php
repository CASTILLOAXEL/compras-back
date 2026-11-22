<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateEstadoSolicitudApiRequest;
use App\Http\Requests\Api\UpdateEstadoSolicitudApiRequest;
use App\Models\EstadoSolicitud;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de estado solicitudes. Cada acción exige su permiso en el token (abilities).
 */
class EstadoSolicitudApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver estado solicitudes', only: ['index', 'show']),
            new Middleware('abilities:crear estado solicitudes', only: ['store']),
            new Middleware('abilities:editar estado solicitudes', only: ['update']),
            new Middleware('abilities:eliminar estado solicitudes', only: ['destroy']),
        ];
    }

    /**
     * GET api/estados_solicitud?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $estadosSolicitud = QueryBuilder::for(EstadoSolicitud::class)
            ->allowedFilters(...[
                'nombre',
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts(...[
                'id',
                'nombre',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($estadosSolicitud, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/estados_solicitud
     */
    public function store(CreateEstadoSolicitudApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $estadoSolicitud = EstadoSolicitud::create($request->validated());

            return $this->sendResponse($estadoSolicitud, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/estados_solicitud/{id}
     */
    public function show(EstadoSolicitud $estadoSolicitud): JsonResponse
    {
        return $this->sendResponse($estadoSolicitud, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/estados_solicitud/{id}
     */
    public function update(UpdateEstadoSolicitudApiRequest $request, EstadoSolicitud $estadoSolicitud): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $estadoSolicitud) {
            $estadoSolicitud->update($request->validated());

            return $this->sendResponse($estadoSolicitud, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/estados_solicitud/{id}  (soft delete)
     */
    public function destroy(EstadoSolicitud $estadoSolicitud): JsonResponse
    {
        return $this->executeOperation(function () use ($estadoSolicitud) {
            $estadoSolicitud->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
