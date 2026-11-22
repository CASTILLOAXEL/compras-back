<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartamentoApiRequest extends FormRequest
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
            'pais_id' => ['required', 'integer', Rule::exists('paises', 'id')->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:200'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
