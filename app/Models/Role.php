<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Rol de Spatie con los nombres de los roles del sistema como constantes.
 */
class Role extends SpatieRole
{
    const SUPER_ADMINISTRADOR = 'Super Administrador';
    const ADMINISTRADOR = 'Administrador';
    const SOLICITANTE = 'Solicitante';
}
