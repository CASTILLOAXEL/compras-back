<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreatePaisApiRequest;
use App\Http\Requests\Api\UpdatePaisApiRequest;
use App\Models\Pais;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de pais. Cada acción exige su permiso en el token (abilities).
 */
class PaisApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver pais', only: ['index', 'show']),
            new Middleware('abilities:crear pais', only: ['store']),
            new Middleware('abilities:editar pais', only: ['update']),
            new Middleware('abilities:eliminar pais', only: ['destroy']),
        ];
    }

    /**
     * GET api/paises?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $paises = QueryBuilder::for(Pais::class)
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

        return $this->sendResponse($paises, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/paises
     */
    public function store(CreatePaisApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $pais = Pais::create($request->validated());

            return $this->sendResponse($pais, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/paises/{id}
     */
    public function show(Pais $pais): JsonResponse
    {
        return $this->sendResponse($pais, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/paises/{id}
     */
    public function update(UpdatePaisApiRequest $request, Pais $pais): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $pais) {
            $pais->update($request->validated());

            return $this->sendResponse($pais, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/paises/{id}  (soft delete)
     */
    public function destroy(Pais $pais): JsonResponse
    {
        return $this->executeOperation(function () use ($pais) {
            $pais->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
