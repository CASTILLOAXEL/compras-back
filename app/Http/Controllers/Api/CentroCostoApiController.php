<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateCentroCostoApiRequest;
use App\Http\Requests\Api\UpdateCentroCostoApiRequest;
use App\Models\CentroCosto;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de centro costos. Cada acción exige su permiso en el token (abilities).
 */
class CentroCostoApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver centro costos', only: ['index', 'show']),
            new Middleware('abilities:crear centro costos', only: ['store']),
            new Middleware('abilities:editar centro costos', only: ['update']),
            new Middleware('abilities:eliminar centro costos', only: ['destroy']),
        ];
    }

    /**
     * GET api/centros_costo?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $centrosCosto = QueryBuilder::for(CentroCosto::class)
            ->allowedFilters([
                'codigo',
                'nombre',
                AllowedFilter::exact('estado'),
            ])
            ->allowedSorts([
                'id',
                'codigo',
                'nombre',
                'estado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($centrosCosto, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/centros_costo
     */
    public function store(CreateCentroCostoApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $centroCosto = CentroCosto::create($request->validated());

            return $this->sendResponse($centroCosto, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/centros_costo/{id}
     */
    public function show(CentroCosto $centroCosto): JsonResponse
    {
        return $this->sendResponse($centroCosto, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/centros_costo/{id}
     */
    public function update(UpdateCentroCostoApiRequest $request, CentroCosto $centroCosto): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $centroCosto) {
            $centroCosto->update($request->validated());

            return $this->sendResponse($centroCosto, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/centros_costo/{id}  (soft delete)
     */
    public function destroy(CentroCosto $centroCosto): JsonResponse
    {
        return $this->executeOperation(function () use ($centroCosto) {
            $centroCosto->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
