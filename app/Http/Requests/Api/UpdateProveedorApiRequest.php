<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProveedorApiRequest extends FormRequest
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
            'razon_social' => ['required', 'string', 'max:400'],
            'nit' => ['required', 'string', 'max:30', Rule::unique('proveedores', 'nit')->ignore($this->route('proveedor'))],
            'dpi' => ['nullable', 'string', 'max:26', Rule::unique('proveedores', 'dpi')->ignore($this->route('proveedor'))],
            'persona_contacto' => ['required', 'string', 'max:300'],
            'telefono' => ['required', 'string', 'max:30'],
            'correo' => ['required', 'string', 'email', 'max:300'],
            'direccion_id' => ['required', 'integer', Rule::exists('direcciones', 'id')->whereNull('deleted_at'), Rule::unique('proveedores', 'direccion_id')->ignore($this->route('proveedor'))],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
