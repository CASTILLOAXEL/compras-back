<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateUnidadMedidaApiRequest;
use App\Http\Requests\Api\UpdateUnidadMedidaApiRequest;
use App\Models\UnidadMedida;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de unidad medidas. Cada acción exige su permiso en el token (abilities).
 */
class UnidadMedidaApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver unidad medidas', only: ['index', 'show']),
            new Middleware('abilities:crear unidad medidas', only: ['store']),
            new Middleware('abilities:editar unidad medidas', only: ['update']),
            new Middleware('abilities:eliminar unidad medidas', only: ['destroy']),
        ];
    }

    /**
     * GET api/unidades_medida?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $unidadesMedida = QueryBuilder::for(UnidadMedida::class)
            ->allowedFilters([
                'nombre',
                'abreviatura',
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts([
                'id',
                'nombre',
                'abreviatura',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($unidadesMedida, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/unidades_medida
     */
    public function store(CreateUnidadMedidaApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $unidadMedida = UnidadMedida::create($request->validated());

            return $this->sendResponse($unidadMedida, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/unidades_medida/{id}
     */
    public function show(UnidadMedida $unidadMedida): JsonResponse
    {
        return $this->sendResponse($unidadMedida, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/unidades_medida/{id}
     */
    public function update(UpdateUnidadMedidaApiRequest $request, UnidadMedida $unidadMedida): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $unidadMedida) {
            $unidadMedida->update($request->validated());

            return $this->sendResponse($unidadMedida, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/unidades_medida/{id}  (soft delete)
     */
    public function destroy(UnidadMedida $unidadMedida): JsonResponse
    {
        return $this->executeOperation(function () use ($unidadMedida) {
            $unidadMedida->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
