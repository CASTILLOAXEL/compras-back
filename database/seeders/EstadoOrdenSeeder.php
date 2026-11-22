<?php

namespace Database\Seeders;

use App\Traits\InsertaConFechasTrait;
use Illuminate\Database\Seeder;

class EstadoOrdenSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public const EMITIDA = 1;
    public const RECIBIDA = 2;
    public const CANCELADA = 3;

    public function run(): void
    {
        $this->insertarConFechas('estados_orden', [
            ['nombre' => 'Emitida'],
            ['nombre' => 'Recibida'],
            ['nombre' => 'Cancelada'],
        ]);
    }
}
