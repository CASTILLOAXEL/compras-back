<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSolicitudApiRequest extends FormRequest
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
            'numero' => ['required', 'string', 'max:40', Rule::unique('solicitudes', 'numero')->ignore($this->route('solicitud'))],
            'fecha' => ['required', 'date'],
            'solicitante_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->whereNull('deleted_at')],
            'centro_costo_id' => ['required', 'integer', Rule::exists('centros_costo', 'id')->whereNull('deleted_at')],
            'estado_solicitud_id' => ['required', 'integer', Rule::exists('estados_solicitud', 'id')->whereNull('deleted_at')],
        ];
    }
}
