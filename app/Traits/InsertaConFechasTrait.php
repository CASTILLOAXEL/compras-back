<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait InsertaConFechasTrait
{
    protected function insertarConFechas(string $tabla, array $registros): void
    {
        $fechaActual = now();

        $registrosConFechas = array_map(
            fn (array $registro) => [...$registro, 'created_at' => $fechaActual, 'updated_at' => $fechaActual],
            $registros
        );

        DB::table($tabla)->insert($registrosConFechas);
    }
}
