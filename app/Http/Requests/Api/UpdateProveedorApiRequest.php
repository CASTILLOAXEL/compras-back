<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rules\Unique;


class UpdateProveedorApiRequest extends CreateProveedorApiRequest
{
    protected function reglaUnica(string $columna): Unique
    {
        return parent::reglaUnica($columna)->ignore($this->route('proveedor'));
    }
}
