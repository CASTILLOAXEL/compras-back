<?php

namespace Database\Seeders;

use App\Traits\InsertaConFechasTrait;
use Illuminate\Database\Seeder;


class PaisSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public function run(): void
    {
        $this->insertarConFechas('paises', [
            ['nombre' => 'Guatemala'],
        ]);
    }
}
