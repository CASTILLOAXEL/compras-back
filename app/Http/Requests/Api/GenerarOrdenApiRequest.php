<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerarOrdenApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Solo proveedores activos y no eliminados
            'proveedor_id' => [
                'required',
                'integer',
                Rule::exists('proveedores', 'id')->where('estado', true)->whereNull('deleted_at'),
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.solicitud_detalle_id' => ['required', 'integer', 'distinct'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.exists' => 'Seleccione un proveedor activo.',
            'items.required' => 'La orden debe tener al menos un ítem.',
            'items.*.cantidad.gt' => 'La cantidad debe ser mayor a 0.',
            'items.*.precio_unitario.min' => 'El precio unitario no puede ser negativo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'proveedor_id' => 'proveedor',
            'items.*.cantidad' => 'cantidad',
            'items.*.precio_unitario' => 'precio unitario',
        ];
    }
}
