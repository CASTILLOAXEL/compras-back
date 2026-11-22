<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos del CRUD de estado solicitudes (generado con generar:crud).
 *
 * Para ejecutar: php artisan db:seed --class=EstadoSolicitudPermisosSeeder
 */
class EstadoSolicitudPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'ver estado solicitudes',
            'crear estado solicitudes',
            'editar estado solicitudes',
            'eliminar estado solicitudes',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'subject' => 'EstadoSolicitud',
                'guard_name' => 'api',
            ]);
        }

        Role::findByName(Role::SUPER_ADMINISTRADOR, 'api')->givePermissionTo($permisos);
        Role::findByName(Role::ADMINISTRADOR, 'api')->givePermissionTo($permisos);
    }
}
