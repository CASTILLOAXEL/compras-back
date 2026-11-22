<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAldeaApiRequest extends FormRequest
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
            'municipio_id' => ['required', 'integer', Rule::exists('municipios', 'id')->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:200'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
