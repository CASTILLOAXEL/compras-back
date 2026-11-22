<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductoApiRequest extends FormRequest
{
    /**
     * El permiso lo valida el middleware "abilities" del controlador.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_producto_id' => ['required', 'integer', Rule::exists('tipos_producto', 'id')->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:300', Rule::unique('productos', 'nombre')],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
