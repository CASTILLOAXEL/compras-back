<?php

namespace Database\Seeders;

use App\Traits\InsertaConFechasTrait;
use Illuminate\Database\Seeder;

class EstadoOrdenSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public function run(): void
    {
        $this->insertarConFechas('estados_orden', [
            ['nombre' => 'Emitida'],
            ['nombre' => 'Recibida'],
            ['nombre' => 'Cancelada'],
        ]);
    }
}
