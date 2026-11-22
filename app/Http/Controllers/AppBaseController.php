<?php

namespace App\Http\Controllers;

use App\Utils\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;


// Controlador base de la API: todas las respuestas salen con el formato { success, data, message }.

class AppBaseController extends Controller
{
    public function sendResponse(mixed $resultado, string $mensaje): JsonResponse
    {
        return response()->json(ResponseUtil::makeResponse($mensaje, $resultado));
    }

    public function sendError(string $mensajeError, int $codigoHttp = 404): JsonResponse
    {
        return response()->json(ResponseUtil::makeError($mensajeError), $codigoHttp);
    }

    public function sendSuccess(string $mensaje): JsonResponse
    {
        return response()->json(ResponseUtil::makeSuccess($mensaje));
    }

    // Ejecuta la operación dentro de una transacción: si algo falla, se revierte todo.
    protected function executeTransaction(callable $operacion): JsonResponse
    {
        try {
            return DB::transaction($operacion);
        } catch (Throwable $excepcion) {
            return $this->sendError(manejarException($excepcion), 500);
        }
    }


   // Ejecuta una operación sin transacción y convierte cualquier error en respuesta JSON.

    protected function executeOperation(callable $operacion): JsonResponse
    {
        try {
            return $operacion();
        } catch (Throwable $excepcion) {
            return $this->sendError(manejarException($excepcion), 500);
        }
    }
}
