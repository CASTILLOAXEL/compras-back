<?php

namespace Database\Seeders;

use App\Traits\InsertaConFechasTrait;
use Illuminate\Database\Seeder;

class EstadoContratoSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public function run(): void
    {
        $this->insertarConFechas('estados_contrato', [
            ['nombre' => 'Vigente'],
            ['nombre' => 'Finalizado'],
            ['nombre' => 'Anulado'],
        ]);
    }
}
