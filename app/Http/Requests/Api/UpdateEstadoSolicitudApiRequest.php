<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstadoSolicitudApiRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:60', Rule::unique('estados_solicitud', 'nombre')->ignore($this->route('estadoSolicitud'))],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
