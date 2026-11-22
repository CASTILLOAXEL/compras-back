<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos del CRUD de pais (generado con generar:crud).
 *
 * Para ejecutar: php artisan db:seed --class=PaisPermisosSeeder
 */
class PaisPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'ver pais',
            'crear pais',
            'editar pais',
            'eliminar pais',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'subject' => 'Pais',
                'guard_name' => 'api',
            ]);
        }

        Role::findByName(Role::SUPER_ADMINISTRADOR, 'api')->givePermissionTo($permisos);
        Role::findByName(Role::ADMINISTRADOR, 'api')->givePermissionTo($permisos);
    }
}
