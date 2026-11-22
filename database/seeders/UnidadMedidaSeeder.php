<?php

namespace Database\Seeders;

use App\Traits\InsertaConFechasTrait;
use Illuminate\Database\Seeder;


class UnidadMedidaSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public function run(): void
    {
        $this->insertarConFechas('unidades_medida', [
            ['nombre' => 'Unidad', 'abreviatura' => 'UND'],
            ['nombre' => 'Caja', 'abreviatura' => 'CJA'],
            ['nombre' => 'Resma', 'abreviatura' => 'RSM'],
            ['nombre' => 'Galón', 'abreviatura' => 'GAL'],
            ['nombre' => 'Hora', 'abreviatura' => 'HR'],
            ['nombre' => 'Servicio', 'abreviatura' => 'SRV'],
        ]);
    }
}
