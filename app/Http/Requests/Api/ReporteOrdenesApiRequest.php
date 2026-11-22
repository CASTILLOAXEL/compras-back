<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ReporteOrdenesApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Todos los filtros son opcionales
    public function rules(): array
    {
        return [
            'proveedor_id' => ['nullable', 'integer'],
            'estado_orden_id' => ['nullable', 'integer'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ];
    }
}
