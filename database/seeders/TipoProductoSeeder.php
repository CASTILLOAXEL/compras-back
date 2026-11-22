<?php

namespace Database\Seeders;

use App\Traits\InsertaConFechasTrait; // NUEVO
use Illuminate\Database\Seeder;

class TipoProductoSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public function run(): void
    {

        $this->insertarConFechas('tipos_producto', [
            ['nombre' => 'Bien'],
            ['nombre' => 'Servicio'],
        ]);
    }
}
