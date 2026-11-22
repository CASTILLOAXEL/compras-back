<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateEstadoContratoApiRequest;
use App\Http\Requests\Api\UpdateEstadoContratoApiRequest;
use App\Models\EstadoContrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de estado contratos. Cada acción exige su permiso en el token (abilities).
 */
class EstadoContratoApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver estado contratos', only: ['index', 'show']),
            new Middleware('abilities:crear estado contratos', only: ['store']),
            new Middleware('abilities:editar estado contratos', only: ['update']),
            new Middleware('abilities:eliminar estado contratos', only: ['destroy']),
        ];
    }

    /**
     * GET api/estados_contrato?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $estadosContrato = QueryBuilder::for(EstadoContrato::class)
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

        return $this->sendResponse($estadosContrato, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/estados_contrato
     */
    public function store(CreateEstadoContratoApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $estadoContrato = EstadoContrato::create($request->validated());

            return $this->sendResponse($estadoContrato, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/estados_contrato/{id}
     */
    public function show(EstadoContrato $estadoContrato): JsonResponse
    {
        return $this->sendResponse($estadoContrato, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/estados_contrato/{id}
     */
    public function update(UpdateEstadoContratoApiRequest $request, EstadoContrato $estadoContrato): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $estadoContrato) {
            $estadoContrato->update($request->validated());

            return $this->sendResponse($estadoContrato, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/estados_contrato/{id}  (soft delete)
     */
    public function destroy(EstadoContrato $estadoContrato): JsonResponse
    {
        return $this->executeOperation(function () use ($estadoContrato) {
            $estadoContrato->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
