<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoProductoApiRequest extends FormRequest
{
    /**
     * El permiso lo valida el middleware "abilities" del controlador.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Los campos únicos ignoran el registro que se está editando.
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('tipos_producto', 'nombre')->ignore($this->route('tipoProducto'))],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
