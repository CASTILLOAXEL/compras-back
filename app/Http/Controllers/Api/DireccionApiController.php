<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateDireccionApiRequest;
use App\Http\Requests\Api\UpdateDireccionApiRequest;
use App\Models\Direccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de direcciones. Cada acción exige su permiso en el token (abilities).
 */
class DireccionApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver direcciones', only: ['index', 'show']),
            new Middleware('abilities:crear direcciones', only: ['store']),
            new Middleware('abilities:editar direcciones', only: ['update']),
            new Middleware('abilities:eliminar direcciones', only: ['destroy']),
        ];
    }

    /**
     * GET api/direcciones?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $direcciones = QueryBuilder::for(Direccion::class)
            ->allowedFilters([
                AllowedFilter::exact('municipio_id'),
                AllowedFilter::exact('aldea_id'),
                'detalle',
            ])
            ->allowedSorts([
                'id',
                'municipio_id',
                'aldea_id',
                'detalle',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($direcciones, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/direcciones
     */
    public function store(CreateDireccionApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $direccion = Direccion::create($request->validated());

            return $this->sendResponse($direccion, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/direcciones/{id}
     */
    public function show(Direccion $direccion): JsonResponse
    {
        return $this->sendResponse($direccion, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/direcciones/{id}
     */
    public function update(UpdateDireccionApiRequest $request, Direccion $direccion): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $direccion) {
            $direccion->update($request->validated());

            return $this->sendResponse($direccion, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/direcciones/{id}  (soft delete)
     */
    public function destroy(Direccion $direccion): JsonResponse
    {
        return $this->executeOperation(function () use ($direccion) {
            $direccion->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
