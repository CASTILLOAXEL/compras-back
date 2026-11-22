<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class OrdenPermisosSeeder extends Seeder
{
    /**
     * Permisos de las órdenes de compra; "recibir ordenes" se usa en la recepción.
     */
    public function run(): void
    {
        $nombresPermisos = ['ver ordenes', 'generar ordenes', 'cancelar ordenes', 'recibir ordenes'];

        foreach ($nombresPermisos as $nombrePermiso) {
            Permission::firstOrCreate(
                ['name' => $nombrePermiso, 'guard_name' => 'api'],
                ['subject' => 'Orden']
            );
        }
    }
}
