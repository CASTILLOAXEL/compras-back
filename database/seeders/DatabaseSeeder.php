<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Para ejecutar: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            // Ubicación
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

            PaisPermisosSeeder::class,
            DepartamentoPermisosSeeder::class,
            MunicipioPermisosSeeder::class,
            AldeaPermisosSeeder::class,
            DireccionPermisosSeeder::class,
            TipoProductoPermisosSeeder::class,
            UnidadMedidaPermisosSeeder::class,
            CentroCostoPermisosSeeder::class,
            ProductoPermisosSeeder::class,
            EstadoSolicitudPermisosSeeder::class,
            ProveedorPermisosSeeder::class,
            SolicitudPermisosSeeder::class,
            SolicitudDetallePermisosSeeder::class,
            EstadoOrdenPermisosSeeder::class,
            EstadoContratoPermisosSeeder::class,
            ContratoPermisosSeeder::class,
            OrdenPermisosSeeder::class,
            // generar:crud permisos

            RolSolicitantePermisosSeeder::class,
        ]);
    }
}
