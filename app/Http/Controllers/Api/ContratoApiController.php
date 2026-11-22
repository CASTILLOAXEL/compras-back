<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateContratoApiRequest;
use App\Http\Requests\Api\UpdateContratoApiRequest;
use App\Models\Contrato;
use App\Models\EstadoContrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Contratos con proveedores (bien o servicio).
 */
class ContratoApiController extends AppBaseController implements HasMiddleware
{
    private const RELACIONES = ['proveedor', 'tipoProducto', 'estadoContrato'];

    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver contratos', only: ['index', 'show']),
            new Middleware('abilities:crear contratos', only: ['store']),
            new Middleware('abilities:editar contratos', only: ['update']),
            new Middleware('abilities:eliminar contratos', only: ['destroy']),
        ];
    }

    public function index(): JsonResponse
    {
        $contratos = QueryBuilder::for(Contrato::class)
            ->with(self::RELACIONES)
            ->allowedFilters(...[
                AllowedFilter::exact('proveedor_id'),
                AllowedFilter::exact('tipo_producto_id'),
                AllowedFilter::exact('estado_contrato_id'),
            ])
            ->allowedSorts(...['id', 'fecha_inicio', 'fecha_fin', 'monto', 'estado_contrato_id'])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($contratos, 'Contratos recuperados con éxito.');
    }

    public function store(CreateContratoApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $contrato = Contrato::create($request->validated());

            return $this->sendResponse($contrato->load(self::RELACIONES), 'Contrato registrado con éxito.');
        });
    }

    public function show(Contrato $contrato): JsonResponse
    {
        return $this->sendResponse($contrato->load(self::RELACIONES), 'Contrato recuperado con éxito.');
    }

    public function update(UpdateContratoApiRequest $request, Contrato $contrato): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $contrato) {
            // Un contrato anulado queda como registro histórico
            if ($contrato->estado_contrato_id === EstadoContrato::ANULADO) {
                throw ValidationException::withMessages(['contrato' => 'Un contrato anulado no se puede modificar.']);
            }

            $contrato->update($request->validated());

            return $this->sendResponse($contrato->load(self::RELACIONES), 'Contrato actualizado con éxito.');
        });
    }

    public function destroy(Contrato $contrato): JsonResponse
    {
        return $this->executeTransaction(function () use ($contrato) {
            $contrato->delete();

            return $this->sendSuccess('Contrato eliminado con éxito.');
        });
    }
}
