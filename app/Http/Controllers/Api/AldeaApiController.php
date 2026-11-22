<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateAldeaApiRequest;
use App\Http\Requests\Api\UpdateAldeaApiRequest;
use App\Models\Aldea;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de aldeas. Cada acción exige su permiso en el token (abilities).
 */
class AldeaApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver aldeas', only: ['index', 'show']),
            new Middleware('abilities:crear aldeas', only: ['store']),
            new Middleware('abilities:editar aldeas', only: ['update']),
            new Middleware('abilities:eliminar aldeas', only: ['destroy']),
        ];
    }

    /**
     * GET api/aldeas?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $aldeas = QueryBuilder::for(Aldea::class)
            ->allowedFilters(...[
                AllowedFilter::exact('municipio_id'),
                'nombre',
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts(...[
                'id',
                'municipio_id',
                'nombre',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($aldeas, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/aldeas
     */
    public function store(CreateAldeaApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $aldea = Aldea::create($request->validated());

            return $this->sendResponse($aldea, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/aldeas/{id}
     */
    public function show(Aldea $aldea): JsonResponse
    {
        return $this->sendResponse($aldea, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/aldeas/{id}
     */
    public function update(UpdateAldeaApiRequest $request, Aldea $aldea): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $aldea) {
            $aldea->update($request->validated());

            return $this->sendResponse($aldea, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/aldeas/{id}  (soft delete)
     */
    public function destroy(Aldea $aldea): JsonResponse
    {
        return $this->executeOperation(function () use ($aldea) {
            $aldea->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
