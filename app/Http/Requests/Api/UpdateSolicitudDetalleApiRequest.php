<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;


class UpdateSolicitudDetalleApiRequest extends CreateSolicitudDetalleApiRequest
{
    public function rules(): array
    {
        return $this->reglasDelItem();
    }

    protected function reglaProductoNoRepetido(): Unique
    {
        $detalle = $this->route('solicitudDetalle');

        return Rule::unique('solicitud_detalles', 'producto_id')
            ->where('solicitud_id', $detalle->solicitud_id)
            ->whereNull('deleted_at')
            ->ignore($detalle);
    }
}
