<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContratoApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => [
                'required',
                'integer',
                Rule::exists('proveedores', 'id')->where('estado', true)->whereNull('deleted_at'),
            ],
            // Bien o servicio
            'tipo_producto_id' => [
                'required',
                'integer',
                Rule::exists('tipos_producto', 'id')->where('estado', true)->whereNull('deleted_at'),
            ],
            'estado_contrato_id' => ['required', 'integer', Rule::exists('estados_contrato', 'id')->whereNull('deleted_at')],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'monto' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.exists' => 'Seleccione un proveedor activo.',
            'tipo_producto_id.exists' => 'Seleccione si el contrato es de un bien o de un servicio.',
            'fecha_fin.after_or_equal' => 'La fecha de finalización no puede ser anterior a la de inicio.',
            'monto.gt' => 'El monto del contrato debe ser mayor a 0.',
        ];
    }

    public function attributes(): array
    {
        return [
            'proveedor_id' => 'proveedor',
            'tipo_producto_id' => 'tipo de contrato',
            'estado_contrato_id' => 'estado del contrato',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de finalización',
        ];
    }
}
