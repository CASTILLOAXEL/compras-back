<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCentroCostoApiRequest extends FormRequest
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
            'codigo' => ['required', 'string', 'max:40', Rule::unique('centros_costo', 'codigo')->ignore($this->route('centroCosto'))],
            'nombre' => ['required', 'string', 'max:300'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
