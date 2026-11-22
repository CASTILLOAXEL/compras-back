<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren token de Sanctum)
|--------------------------------------------------------------------------
| generar:crud agrega aquí cada Route::apiResource. No borres la marca.
*/
Route::middleware('auth:sanctum')->group(function () {
    // generar:crud rutas
});
