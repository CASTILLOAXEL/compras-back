<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos del CRUD de productos (generado con generar:crud).
 *
 * Para ejecutar: php artisan db:seed --class=ProductoPermisosSeeder
 */
class ProductoPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'ver productos',
            'crear productos',
            'editar productos',
            'eliminar productos',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'subject' => 'Producto',
                'guard_name' => 'api',
            ]);
        }

        Role::findByName(Role::SUPER_ADMINISTRADOR, 'api')->givePermissionTo($permisos);
        Role::findByName(Role::ADMINISTRADOR, 'api')->givePermissionTo($permisos);
    }
}
