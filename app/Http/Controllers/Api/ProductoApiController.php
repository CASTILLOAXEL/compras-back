<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateProductoApiRequest;
use App\Http\Requests\Api\UpdateProductoApiRequest;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de productos. Cada acción exige su permiso en el token (abilities).
 */
class ProductoApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver productos', only: ['index', 'show']),
            new Middleware('abilities:crear productos', only: ['store']),
            new Middleware('abilities:editar productos', only: ['update']),
            new Middleware('abilities:eliminar productos', only: ['destroy']),
        ];
    }

    /**
     * GET api/productos?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $productos = QueryBuilder::for(Producto::class)
            ->allowedFilters(...[
                AllowedFilter::exact('tipo_producto_id'),
                'nombre',
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts(...[
                'id',
                'tipo_producto_id',
                'nombre',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($productos, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/productos
     */
    public function store(CreateProductoApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $producto = Producto::create($request->validated());

            return $this->sendResponse($producto, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/productos/{id}
     */
    public function show(Producto $producto): JsonResponse
    {
        return $this->sendResponse($producto, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/productos/{id}
     */
    public function update(UpdateProductoApiRequest $request, Producto $producto): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $producto) {
            $producto->update($request->validated());

            return $this->sendResponse($producto, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/productos/{id}  (soft delete)
     */
    public function destroy(Producto $producto): JsonResponse
    {
        return $this->executeOperation(function () use ($producto) {
            $producto->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
