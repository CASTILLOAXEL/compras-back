<?php

use Illuminate\Support\Facades\Log;

if (! function_exists('manejarException')) {
    /**
     * Registra la excepción en el log y devuelve el mensaje que puede ver el usuario.
     * Con APP_DEBUG=true muestra el detalle; en producción, un mensaje genérico.
     */
    function manejarException(Throwable $excepcion): string
    {
        Log::error($excepcion->getMessage(), [
            'archivo' => $excepcion->getFile() . ' Línea: ' . $excepcion->getLine(),
            'request' => request()->except(['password', 'password_confirmation']),
            'usuario_id' => auth()->id(),
        ]);

        return config('app.debug')
            ? $excepcion->getMessage()
            : 'Hubo un error, intente de nuevo.';
    }
}
