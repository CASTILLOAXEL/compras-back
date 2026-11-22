<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateProveedorApiRequest;
use App\Http\Requests\Api\UpdateProveedorApiRequest;
use App\Models\Direccion;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProveedorApiController extends AppBaseController implements HasMiddleware
{

    private const RELACIONES = ['direccion.municipio.departamento', 'direccion.aldea'];

    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver proveedores', only: ['index', 'show']),
            new Middleware('abilities:crear proveedores', only: ['store']),
            new Middleware('abilities:editar proveedores', only: ['update']),
            new Middleware('abilities:eliminar proveedores', only: ['destroy']),
        ];
    }

    public function index(): JsonResponse
    {
        $proveedores = QueryBuilder::for(Proveedor::class)
            ->with(self::RELACIONES)
            ->allowedFilters([
                'razon_social',
                'nit',
                'dpi',
                'persona_contacto',
                'telefono',
                'correo',
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts([
                'id',
                'razon_social',
                'nit',
                'persona_contacto',
                'correo',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($proveedores, 'Proveedores recuperados con éxito.');
    }

    public function store(CreateProveedorApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $direccion = Direccion::create($request->validated('direccion'));

            $proveedor = Proveedor::create([
                ...$request->safe()->except('direccion'),
                'direccion_id' => $direccion->id,
            ]);

            return $this->sendResponse($proveedor->load(self::RELACIONES), 'Proveedor creado con éxito.');
        });
    }


    public function show(Proveedor $proveedor): JsonResponse
    {
        return $this->sendResponse($proveedor->load(self::RELACIONES), 'Proveedor recuperado con éxito.');
    }


    public function update(UpdateProveedorApiRequest $request, Proveedor $proveedor): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $proveedor) {
            $proveedor->direccion->update($request->validated('direccion'));
            $proveedor->update($request->safe()->except('direccion'));

            return $this->sendResponse($proveedor->load(self::RELACIONES), 'Proveedor actualizado con éxito.');
        });
    }

    public function destroy(Proveedor $proveedor): JsonResponse
    {
        return $this->executeOperation(function () use ($proveedor) {
            $proveedor->delete();

            return $this->sendSuccess('Proveedor eliminado con éxito.');
        });
    }
}
