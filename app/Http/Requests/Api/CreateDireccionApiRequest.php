<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateDireccionApiRequest extends FormRequest
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
            'municipio_id' => ['required', 'integer', Rule::exists('municipios', 'id')->whereNull('deleted_at')],
            'aldea_id' => ['nullable', 'integer', Rule::exists('aldeas', 'id')->whereNull('deleted_at')],
            'detalle' => ['required', 'string', 'max:500'],
        ];
    }
}
