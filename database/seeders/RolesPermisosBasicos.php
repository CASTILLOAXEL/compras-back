<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesPermisosBasicos extends Seeder
{
    public function run(): void
    {

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdministrador = Role::firstOrCreate(['name' => Role::SUPER_ADMINISTRADOR, 'guard_name' => 'api']);
        $administrador = Role::firstOrCreate(['name' => Role::ADMINISTRADOR, 'guard_name' => 'api']);
        $solicitante = Role::firstOrCreate(['name' => Role::SOLICITANTE, 'guard_name' => 'api']);

        Permission::firstOrCreate(['name' => 'home', 'subject' => 'home', 'guard_name' => 'api']);

        $superAdministrador->syncPermissions(Permission::all());
        $administrador->syncPermissions(Permission::all());
        $solicitante->syncPermissions(['home']);
    }
}
