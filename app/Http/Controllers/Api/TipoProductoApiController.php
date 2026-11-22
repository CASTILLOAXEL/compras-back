<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateTipoProductoApiRequest;
use App\Http\Requests\Api\UpdateTipoProductoApiRequest;
use App\Models\TipoProducto;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de tipo productos. Cada acción exige su permiso en el token (abilities).
 */
class TipoProductoApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver tipo productos', only: ['index', 'show']),
            new Middleware('abilities:crear tipo productos', only: ['store']),
            new Middleware('abilities:editar tipo productos', only: ['update']),
            new Middleware('abilities:eliminar tipo productos', only: ['destroy']),
        ];
    }

    /**
     * GET api/tipos_producto?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $tiposProducto = QueryBuilder::for(TipoProducto::class)
            ->allowedFilters([
                'nombre',
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts([
                'id',
                'nombre',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($tiposProducto, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/tipos_producto
     */
    public function store(CreateTipoProductoApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $tipoProducto = TipoProducto::create($request->validated());

            return $this->sendResponse($tipoProducto, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/tipos_producto/{id}
     */
    public function show(TipoProducto $tipoProducto): JsonResponse
    {
        return $this->sendResponse($tipoProducto, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/tipos_producto/{id}
     */
    public function update(UpdateTipoProductoApiRequest $request, TipoProducto $tipoProducto): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $tipoProducto) {
            $tipoProducto->update($request->validated());

            return $this->sendResponse($tipoProducto, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/tipos_producto/{id}  (soft delete)
     */
    public function destroy(TipoProducto $tipoProducto): JsonResponse
    {
        return $this->executeOperation(function () use ($tipoProducto) {
            $tipoProducto->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
