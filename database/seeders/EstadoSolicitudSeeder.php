<?php

namespace Database\Seeders;

use App\Traits\InsertaConFechasTrait;
use Illuminate\Database\Seeder;


class EstadoSolicitudSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public function run(): void
    {
        $this->insertarConFechas('estados_solicitud', [
            ['nombre' => 'Borrador'],
            ['nombre' => 'En aprobación'],
            ['nombre' => 'Aprobada'],
            ['nombre' => 'Rechazada'],
            ['nombre' => 'Completada'],
        ]);
    }
}
