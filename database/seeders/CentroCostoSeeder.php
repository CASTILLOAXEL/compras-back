<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Traits\InsertaConFechasTrait;

class CentroCostoSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public function run(): void
    {
        $this->insertarConFechas('centros_costo', [
            ['codigo' => 'CC-001', 'nombre' => 'Gerencia General'],
            ['codigo' => 'CC-002', 'nombre' => 'Tecnología'],
            ['codigo' => 'CC-003', 'nombre' => 'Créditos'],
            ['codigo' => 'CC-004', 'nombre' => 'Agencias'],
            ['codigo' => 'CC-005', 'nombre' => 'Recursos Humanos'],
        ]);
    }
}
