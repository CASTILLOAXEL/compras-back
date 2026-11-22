<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos del rol Solicitante: crea y envía sus solicitudes,
 * y consulta los catálogos que usa el formulario. No puede aprobar.
 * Requiere que los seeders de permisos de cada CRUD ya se hayan ejecutado.
 *
 *
 */
class RolSolicitantePermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findByName(Role::SOLICITANTE, 'api')->givePermissionTo([
            // Solicitudes propias
            'ver solicitudes',
            'crear solicitudes',
            'editar solicitudes',
            'eliminar solicitudes',
            'enviar solicitudes',

            // Ítems de la solicitud
            'ver solicitud detalles',
            'crear solicitud detalles',
            'editar solicitud detalles',
            'eliminar solicitud detalles',

            // Catálogos para los combos del formulario
            'ver centro costos',
            'ver productos',
            'ver unidad medidas',
        ]);
    }
}
