<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;


class CreateProveedorApiRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'razon_social' => ['required', 'string', 'max:200'],
            'nit' => ['required', 'string', 'max:15', 'regex:/^\d+-?[\dK]$/i', $this->reglaUnica('nit')],
            'dpi' => ['nullable', 'digits:13', $this->reglaUnica('dpi')],
            'persona_contacto' => ['required', 'string', 'max:150'],
            'telefono' => ['required', 'regex:/^\d{8}$/'],
            'correo' => ['required', 'email', 'max:150'],
            'estado' => ['sometimes', 'boolean'],


            'direccion' => ['required', 'array'],
            'direccion.municipio_id' => ['required', 'integer', Rule::exists('municipios', 'id')->whereNull('deleted_at')],
            'direccion.aldea_id' => [
                'nullable',
                'integer',
                // La aldea debe pertenecer al municipio elegido
                Rule::exists('aldeas', 'id')
                    ->where('municipio_id', $this->input('direccion.municipio_id'))
                    ->whereNull('deleted_at'),
            ],
            'direccion.detalle' => ['required', 'string', 'max:250'],
        ];
    }

    public function messages(): array
    {
        return [
            'nit.regex' => 'El NIT solo debe tener números y el dígito verificador (ej. 1234567-8 o 123456K).',
            'telefono.regex' => 'El teléfono debe tener 8 dígitos.',
            'direccion.aldea_id.exists' => 'La aldea no pertenece al municipio seleccionado.',
        ];
    }

    public function attributes(): array
    {
        return [
            'razon_social' => 'razón social',
            'persona_contacto' => 'persona de contacto',
            'telefono' => 'teléfono',
            'direccion.municipio_id' => 'municipio',
            'direccion.aldea_id' => 'aldea',
            'direccion.detalle' => 'dirección',
        ];
    }

    /**
     * En edición se sobrescribe para ignorar el proveedor actual.
     */
    protected function reglaUnica(string $columna): Unique
    {
        return Rule::unique('proveedores', $columna);
    }
}
