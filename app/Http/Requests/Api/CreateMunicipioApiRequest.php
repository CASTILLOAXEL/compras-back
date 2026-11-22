<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMunicipioApiRequest extends FormRequest
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
            'departamento_id' => ['required', 'integer', Rule::exists('departamentos', 'id')->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:200'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
