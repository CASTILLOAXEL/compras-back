<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateEstadoOrdenApiRequest;
use App\Http\Requests\Api\UpdateEstadoOrdenApiRequest;
use App\Models\EstadoOrden;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de estado ordenes. Cada acción exige su permiso en el token (abilities).
 */
class EstadoOrdenApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver estado ordenes', only: ['index', 'show']),
            new Middleware('abilities:crear estado ordenes', only: ['store']),
            new Middleware('abilities:editar estado ordenes', only: ['update']),
            new Middleware('abilities:eliminar estado ordenes', only: ['destroy']),
        ];
    }

    /**
     * GET api/estados_orden?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $estadosOrden = QueryBuilder::for(EstadoOrden::class)
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

        return $this->sendResponse($estadosOrden, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/estados_orden
     */
    public function store(CreateEstadoOrdenApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $estadoOrden = EstadoOrden::create($request->validated());

            return $this->sendResponse($estadoOrden, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/estados_orden/{id}
     */
    public function show(EstadoOrden $estadoOrden): JsonResponse
    {
        return $this->sendResponse($estadoOrden, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/estados_orden/{id}
     */
    public function update(UpdateEstadoOrdenApiRequest $request, EstadoOrden $estadoOrden): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $estadoOrden) {
            $estadoOrden->update($request->validated());

            return $this->sendResponse($estadoOrden, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/estados_orden/{id}  (soft delete)
     */
    public function destroy(EstadoOrden $estadoOrden): JsonResponse
    {
        return $this->executeOperation(function () use ($estadoOrden) {
            $estadoOrden->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
