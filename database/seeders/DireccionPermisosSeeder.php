<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos del CRUD de direcciones (generado con generar:crud).
 *
 * Para ejecutar: php artisan db:seed --class=DireccionPermisosSeeder
 */
class DireccionPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'ver direcciones',
            'crear direcciones',
            'editar direcciones',
            'eliminar direcciones',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'subject' => 'Direccion',
                'guard_name' => 'api',
            ]);
        }

        Role::findByName(Role::SUPER_ADMINISTRADOR, 'api')->givePermissionTo($permisos);
        Role::findByName(Role::ADMINISTRADOR, 'api')->givePermissionTo($permisos);
    }
}
