<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Permiso de Spatie con "subject": acción + sujeto para CASL en el front.
 *  name = 'crear proveedores', subject = 'Proveedor'.
 */
class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'subject', 'guard_name'];
}
