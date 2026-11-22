<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Traits\InsertaConFechasTrait;
class DepartamentoSeeder extends Seeder
{
    use InsertaConFechasTrait;

    public function run(): void
    {
        $paisId = DB::table('paises')->where('nombre', 'Guatemala')->value('id');

        $departamentos = [
            'Alta Verapaz', 'Baja Verapaz', 'Chimaltenango', 'Chiquimula', 'El Progreso',
            'Escuintla', 'Guatemala', 'Huehuetenango', 'Izabal', 'Jalapa', 'Jutiapa',
            'Petén', 'Quetzaltenango', 'Quiché', 'Retalhuleu', 'Sacatepéquez',
            'San Marcos', 'Santa Rosa', 'Sololá', 'Suchitepéquez', 'Totonicapán', 'Zacapa',
        ];

        $this->insertarConFechas('departamentos', array_map(
            fn (string $nombreDepartamento) => ['pais_id' => $paisId, 'nombre' => $nombreDepartamento],
            $departamentos
        ));
    }
}
