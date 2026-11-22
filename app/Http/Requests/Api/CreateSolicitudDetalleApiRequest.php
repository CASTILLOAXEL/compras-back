<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;


class CreateSolicitudDetalleApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solicitud_id' => ['required', 'integer', Rule::exists('solicitudes', 'id')->whereNull('deleted_at')],
            ...$this->reglasDelItem(),
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.unique' => 'Este producto ya está en la solicitud; modifique la cantidad del ítem existente.',
        ];
    }

    public function attributes(): array
    {
        return [
            'solicitud_id' => 'solicitud',
            'producto_id' => 'producto',
            'unidad_medida_id' => 'unidad de medida',
            'precio_estimado' => 'precio estimado',
        ];
    }

    /**
     * Reglas compartidas con la edición.
     */
    protected function reglasDelItem(): array
    {
        return [
            'producto_id' => [
                'required',
                'integer',
                Rule::exists('productos', 'id')->where('estado', true)->whereNull('deleted_at'),
                $this->reglaProductoNoRepetido(),
            ],
            'unidad_medida_id' => [
                'required',
                'integer',
                Rule::exists('unidades_medida', 'id')->where('estado', true)->whereNull('deleted_at'),
            ],
            'cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:9999999999.99'],
            'precio_estimado' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:99999999999.99'],
        ];
    }

    /**
     * Un producto no se repite dentro de la misma solicitud.
     */
    protected function reglaProductoNoRepetido(): Unique
    {
        return Rule::unique('solicitud_detalles', 'producto_id')
            ->where('solicitud_id', $this->input('solicitud_id'))
            ->whereNull('deleted_at');
    }
}
