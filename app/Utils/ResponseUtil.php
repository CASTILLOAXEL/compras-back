<?php

namespace App\Utils;

/**
 * Arma el cuerpo de las respuestas JSON con el formato estándar { success, data, message }.
 */
class ResponseUtil
{
    public static function makeResponse(string $mensaje, mixed $datos): array
    {
        return [
            'success' => true,
            'data' => $datos,
            'message' => $mensaje,
        ];
    }

    public static function makeError(string $mensaje, array $datos = []): array
    {
        $respuesta = [
            'success' => false,
            'message' => $mensaje,
        ];

        if (! empty($datos)) {
            $respuesta['data'] = $datos;
        }

        return $respuesta;
    }

    public static function makeSuccess(string $mensaje): array
    {
        return [
            'success' => true,
            'message' => $mensaje,
        ];
    }
}
