<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUnidadMedidaApiRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:100', Rule::unique('unidades_medida', 'nombre')],
            'abreviatura' => ['required', 'string', 'max:20', Rule::unique('unidades_medida', 'abreviatura')],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
