<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren token de Sanctum)
|--------------------------------------------------------------------------
| generar:crud agrega aquí cada Route::apiResource. No borres la marca.
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('paises', App\Http\Controllers\Api\PaisApiController::class)->parameters(['paises' => 'pais']);
    Route::apiResource('departamentos', App\Http\Controllers\Api\DepartamentoApiController::class)->parameters(['departamentos' => 'departamento']);
    Route::apiResource('municipios', App\Http\Controllers\Api\MunicipioApiController::class)->parameters(['municipios' => 'municipio']);
    Route::apiResource('aldeas', App\Http\Controllers\Api\AldeaApiController::class)->parameters(['aldeas' => 'aldea']);
    Route::apiResource('direcciones', App\Http\Controllers\Api\DireccionApiController::class)->parameters(['direcciones' => 'direccion']);
    Route::apiResource('tipos_producto', App\Http\Controllers\Api\TipoProductoApiController::class)->parameters(['tipos_producto' => 'tipoProducto']);
    Route::apiResource('unidades_medida', App\Http\Controllers\Api\UnidadMedidaApiController::class)->parameters(['unidades_medida' => 'unidadMedida']);
    Route::apiResource('centros_costo', App\Http\Controllers\Api\CentroCostoApiController::class)->parameters(['centros_costo' => 'centroCosto']);
    Route::apiResource('productos', App\Http\Controllers\Api\ProductoApiController::class)->parameters(['productos' => 'producto']);
    Route::apiResource('estados_solicitud', App\Http\Controllers\Api\EstadoSolicitudApiController::class)->parameters(['estados_solicitud' => 'estadoSolicitud']);
    Route::apiResource('proveedores', App\Http\Controllers\Api\ProveedorApiController::class)->parameters(['proveedores' => 'proveedor']);
    Route::apiResource('solicitudes', App\Http\Controllers\Api\SolicitudApiController::class)->parameters(['solicitudes' => 'solicitud']);
    Route::apiResource('solicitud_detalles', App\Http\Controllers\Api\SolicitudDetalleApiController::class)->parameters(['solicitud_detalles' => 'solicitudDetalle']);
    // generar:crud rutas
});
