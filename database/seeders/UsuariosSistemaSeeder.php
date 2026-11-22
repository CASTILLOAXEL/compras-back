<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Database\Seeder;


class UsuariosSistemaSeeder extends Seeder
{
    public function run(): void
    {
        $superAdministrador = Usuario::firstOrCreate(
            ['correo' => 'admin@compras.test'],
            [
                'dpi' => '1000000000101',
                'primer_nombre' => 'Administrador',
                'primer_apellido' => 'Sistema',
                'password' => 'Gy202600',
            ]
        );
        $superAdministrador->assignRole(Role::SUPER_ADMINISTRADOR);

        $solicitante = Usuario::firstOrCreate(
            ['correo' => 'solicitante@compras.test'],
            [
                'dpi' => '1000000000202',
                'primer_nombre' => 'Ana',
                'segundo_nombre' => 'María',
                'primer_apellido' => 'López',
                'password' => 'Gy202600',
            ]
        );
        $solicitante->assignRole(Role::SOLICITANTE);
    }
}
