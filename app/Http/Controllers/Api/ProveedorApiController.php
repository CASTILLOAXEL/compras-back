<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateProveedorApiRequest;
use App\Http\Requests\Api\UpdateProveedorApiRequest;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de proveedores. Cada acción exige su permiso en el token (abilities).
 */
class ProveedorApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver proveedores', only: ['index', 'show']),
            new Middleware('abilities:crear proveedores', only: ['store']),
            new Middleware('abilities:editar proveedores', only: ['update']),
            new Middleware('abilities:eliminar proveedores', only: ['destroy']),
        ];
    }

    /**
     * GET api/proveedores?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $proveedores = QueryBuilder::for(Proveedor::class)
            ->allowedFilters([
                'razon_social',
                'nit',
                'dpi',
                'persona_contacto',
                'telefono',
                'correo',
                AllowedFilter::exact('direccion_id'),
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts([
                'id',
                'razon_social',
                'nit',
                'dpi',
                'persona_contacto',
                'telefono',
                'correo',
                'direccion_id',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($proveedores, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/proveedores
     */
    public function store(CreateProveedorApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $proveedor = Proveedor::create($request->validated());

            return $this->sendResponse($proveedor, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/proveedores/{id}
     */
    public function show(Proveedor $proveedor): JsonResponse
    {
        return $this->sendResponse($proveedor, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/proveedores/{id}
     */
    public function update(UpdateProveedorApiRequest $request, Proveedor $proveedor): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $proveedor) {
            $proveedor->update($request->validated());

            return $this->sendResponse($proveedor, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/proveedores/{id}  (soft delete)
     */
    public function destroy(Proveedor $proveedor): JsonResponse
    {
        return $this->executeOperation(function () use ($proveedor) {
            $proveedor->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
