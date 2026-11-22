<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class CreateSolicitudApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'centro_costo_id' => [
                'required',
                'integer',
                // Solo centros de costo activos y no eliminados
                Rule::exists('centros_costo', 'id')->where('estado', true)->whereNull('deleted_at'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'centro_costo_id' => 'centro de costo',
        ];
    }
}
