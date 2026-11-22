<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarRecepcionApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.orden_detalle_id' => ['required', 'integer', 'distinct'],
            'items.*.cantidad_recibida' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.before_or_equal' => 'La fecha de recepción no puede ser futura.',
            'items.required' => 'Indique al menos un ítem recibido.',
            'items.*.cantidad_recibida.gt' => 'La cantidad recibida debe ser mayor a 0.',
        ];
    }

    public function attributes(): array
    {
        return [
            'fecha' => 'fecha de recepción',
            'items.*.cantidad_recibida' => 'cantidad recibida',
        ];
    }
}
