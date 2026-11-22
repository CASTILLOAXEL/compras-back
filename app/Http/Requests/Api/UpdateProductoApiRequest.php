<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoApiRequest extends FormRequest
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
            'tipo_producto_id' => ['required', 'integer', Rule::exists('tipos_producto', 'id')->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:300', Rule::unique('productos', 'nombre')->ignore($this->route('producto'))],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
