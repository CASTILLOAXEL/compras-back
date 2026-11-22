<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos del CRUD de departamentos (generado con generar:crud).
 *
 * Para ejecutar: php artisan db:seed --class=DepartamentoPermisosSeeder
 */
class DepartamentoPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'ver departamentos',
            'crear departamentos',
            'editar departamentos',
            'eliminar departamentos',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'subject' => 'Departamento',
                'guard_name' => 'api',
            ]);
        }

        Role::findByName(Role::SUPER_ADMINISTRADOR, 'api')->givePermissionTo($permisos);
        Role::findByName(Role::ADMINISTRADOR, 'api')->givePermissionTo($permisos);
    }
}
