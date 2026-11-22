<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateDepartamentoApiRequest;
use App\Http\Requests\Api\UpdateDepartamentoApiRequest;
use App\Models\Departamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de departamentos. Cada acción exige su permiso en el token (abilities).
 */
class DepartamentoApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver departamentos', only: ['index', 'show']),
            new Middleware('abilities:crear departamentos', only: ['store']),
            new Middleware('abilities:editar departamentos', only: ['update']),
            new Middleware('abilities:eliminar departamentos', only: ['destroy']),
        ];
    }

    /**
     * GET api/departamentos?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $departamentos = QueryBuilder::for(Departamento::class)
            ->allowedFilters(...[
                AllowedFilter::exact('pais_id'),
                'nombre',
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts(...[
                'id',
                'pais_id',
                'nombre',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($departamentos, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/departamentos
     */
    public function store(CreateDepartamentoApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $departamento = Departamento::create($request->validated());

            return $this->sendResponse($departamento, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/departamentos/{id}
     */
    public function show(Departamento $departamento): JsonResponse
    {
        return $this->sendResponse($departamento, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/departamentos/{id}
     */
    public function update(UpdateDepartamentoApiRequest $request, Departamento $departamento): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $departamento) {
            $departamento->update($request->validated());

            return $this->sendResponse($departamento, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/departamentos/{id}  (soft delete)
     */
    public function destroy(Departamento $departamento): JsonResponse
    {
        return $this->executeOperation(function () use ($departamento) {
            $departamento->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
