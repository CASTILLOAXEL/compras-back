<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateMunicipioApiRequest;
use App\Http\Requests\Api\UpdateMunicipioApiRequest;
use App\Models\Municipio;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de municipios. Cada acción exige su permiso en el token (abilities).
 */
class MunicipioApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver municipios', only: ['index', 'show']),
            new Middleware('abilities:crear municipios', only: ['store']),
            new Middleware('abilities:editar municipios', only: ['update']),
            new Middleware('abilities:eliminar municipios', only: ['destroy']),
        ];
    }

    /**
     * GET api/municipios?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $municipios = QueryBuilder::for(Municipio::class)
            ->allowedFilters([
                AllowedFilter::exact('departamento_id'),
                'nombre',
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts([
                'id',
                'departamento_id',
                'nombre',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($municipios, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/municipios
     */
    public function store(CreateMunicipioApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $municipio = Municipio::create($request->validated());

            return $this->sendResponse($municipio, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/municipios/{id}
     */
    public function show(Municipio $municipio): JsonResponse
    {
        return $this->sendResponse($municipio, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/municipios/{id}
     */
    public function update(UpdateMunicipioApiRequest $request, Municipio $municipio): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $municipio) {
            $municipio->update($request->validated());

            return $this->sendResponse($municipio, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/municipios/{id}  (soft delete)
     */
    public function destroy(Municipio $municipio): JsonResponse
    {
        return $this->executeOperation(function () use ($municipio) {
            $municipio->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
