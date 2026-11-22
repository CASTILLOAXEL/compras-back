<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

        $this->call([
            // Catálogos de geografía
            PaisSeeder::class,
            DepartamentoSeeder::class,
            MunicipioSeeder::class,
            AldeaSeeder::class,

            // Catálogos de compras
            TipoProductoSeeder::class,
            UnidadMedidaSeeder::class,
            CentroCostoSeeder::class,
            ProductoSeeder::class,

            // Estados
            EstadoSolicitudSeeder::class,
            EstadoOrdenSeeder::class,
            EstadoContratoSeeder::class,

            // Seguridad
            RolesPermisosBasicos::class,
            UsuariosSistemaSeeder::class,
        ]);
    }
}
