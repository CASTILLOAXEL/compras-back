<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaisApiRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:200', Rule::unique('paises', 'nombre')->ignore($this->route('pais'))],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
