<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSolicitudDetalleApiRequest extends FormRequest
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
            'solicitud_id' => ['required', 'integer', Rule::exists('solicitudes', 'id')->whereNull('deleted_at')],
            'producto_id' => ['required', 'integer', Rule::exists('productos', 'id')->whereNull('deleted_at')],
            'unidad_medida_id' => ['required', 'integer', Rule::exists('unidades_medida', 'id')->whereNull('deleted_at')],
            'cantidad' => ['required', 'numeric'],
            'precio_estimado' => ['required', 'numeric'],
        ];
    }
}
