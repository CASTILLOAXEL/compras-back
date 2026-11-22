<?php

use App\Http\Controllers\Api\Auth\AuthApiController;
use App\Http\Controllers\Api\PaisApiController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->name('api.auth.')->group(function () {
    // Rutas de autenticación
    Route::post('login', [AuthApiController::class, 'login'])->middleware('throttle:5,1')->name('login');
    Route::post('logout', [AuthApiController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
});

Route::get('user', [AuthApiController::class, 'usuario'])->middleware('auth:sanctum')->name('api.user');

// Rutas protegidas por autenticación
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('paises', PaisApiController::class)->parameters(['paises' => 'pais']);
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
    Route::apiResource('estados_orden', App\Http\Controllers\Api\EstadoOrdenApiController::class)->parameters(['estados_orden' => 'estadoOrden']);
    Route::apiResource('estados_contrato', App\Http\Controllers\Api\EstadoContratoApiController::class)->parameters(['estados_contrato' => 'estadoContrato']);
    Route::apiResource('contratos', App\Http\Controllers\Api\ContratoApiController::class)->parameters(['contratos' => 'contrato']);
    // generar:crud rutas

    // Rutas para el flujo de aprobación de solicitudes
    Route::prefix('solicitudes/{solicitud}')->name('solicitudes.')->group(function () {
        Route::post('enviar', [App\Http\Controllers\Api\SolicitudApiController::class, 'enviar'])->name('enviar');
        Route::post('aprobar', [App\Http\Controllers\Api\SolicitudApiController::class, 'aprobar'])->name('aprobar');
        Route::post('rechazar', [App\Http\Controllers\Api\SolicitudApiController::class, 'rechazar'])->name('rechazar');

        // Una solicitud aprobada genera su orden de compra
        Route::post('orden', [App\Http\Controllers\Api\OrdenApiController::class, 'generarDesdeSolicitud'])->name('orden');
    });

    // Órdenes de compra (no se crean ni editan directo: nacen de una solicitud y solo se cancelan)
    Route::prefix('ordenes')->name('ordenes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\OrdenApiController::class, 'index'])->name('index');
        Route::get('{orden}', [App\Http\Controllers\Api\OrdenApiController::class, 'show'])->name('show');
        Route::post('{orden}/cancelar', [App\Http\Controllers\Api\OrdenApiController::class, 'cancelar'])->name('cancelar');
        Route::post('{orden}/recepciones', [App\Http\Controllers\Api\OrdenApiController::class, 'registrarRecepcion'])->name('recepciones'); // NUEVO
    });

    //  reporte e indicadores
    Route::get('reportes/ordenes', [App\Http\Controllers\Api\ReporteApiController::class, 'ordenesPorProveedorYEstado'])->name('reportes.ordenes');
    Route::get('inicio/indicadores', [App\Http\Controllers\Api\ReporteApiController::class, 'indicadoresInicio'])->name('inicio.indicadores');
});
