<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Api\CreateSolicitudDetalleApiRequest;
use App\Http\Requests\Api\UpdateSolicitudDetalleApiRequest;
use App\Models\SolicitudDetalle;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * CRUD de solicitud detalles. Cada acción exige su permiso en el token (abilities).
 */
class SolicitudDetalleApiController extends AppBaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:ver solicitud detalles', only: ['index', 'show']),
            new Middleware('abilities:crear solicitud detalles', only: ['store']),
            new Middleware('abilities:editar solicitud detalles', only: ['update']),
            new Middleware('abilities:eliminar solicitud detalles', only: ['destroy']),
        ];
    }

    /**
     * GET api/solicitud_detalles?page[number]=1&page[size]=10&filter[campo]=valor&sort=-id
     */
    public function index(): JsonResponse
    {
        $solicitudDetalles = QueryBuilder::for(SolicitudDetalle::class)
            ->allowedFilters([
                AllowedFilter::exact('solicitud_id'),
                AllowedFilter::exact('producto_id'),
                AllowedFilter::exact('unidad_medida_id'),
                AllowedFilter::exact('cantidad'),
                AllowedFilter::exact('precio_estimado'),
            ])
            ->allowedSorts([
                'id',
                'solicitud_id',
                'producto_id',
                'unidad_medida_id',
                'cantidad',
                'precio_estimado',
            ])
            ->defaultSort('-id')
            ->jsonPaginate();

        return $this->sendResponse($solicitudDetalles, 'Registros recuperados con éxito.');
    }

    /**
     * POST api/solicitud_detalles
     */
    public function store(CreateSolicitudDetalleApiRequest $request): JsonResponse
    {
        return $this->executeTransaction(function () use ($request) {
            $solicitudDetalle = SolicitudDetalle::create($request->validated());

            return $this->sendResponse($solicitudDetalle, 'Registro creado con éxito.');
        });
    }

    /**
     * GET api/solicitud_detalles/{id}
     */
    public function show(SolicitudDetalle $solicitudDetalle): JsonResponse
    {
        return $this->sendResponse($solicitudDetalle, 'Registro recuperado con éxito.');
    }

    /**
     * PUT/PATCH api/solicitud_detalles/{id}
     */
    public function update(UpdateSolicitudDetalleApiRequest $request, SolicitudDetalle $solicitudDetalle): JsonResponse
    {
        return $this->executeTransaction(function () use ($request, $solicitudDetalle) {
            $solicitudDetalle->update($request->validated());

            return $this->sendResponse($solicitudDetalle, 'Registro actualizado con éxito.');
        });
    }

    /**
     * DELETE api/solicitud_detalles/{id}  (soft delete)
     */
    public function destroy(SolicitudDetalle $solicitudDetalle): JsonResponse
    {
        return $this->executeOperation(function () use ($solicitudDetalle) {
            $solicitudDetalle->delete();

            return $this->sendSuccess('Registro eliminado con éxito.');
        });
    }
}
