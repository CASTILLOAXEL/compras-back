<?php

namespace App\Services\Services;

use App\Models\EstadoOrden;
use App\Models\Orden;
use App\Models\OrdenDetalle;
use App\Models\Recepcion;
use App\Models\Usuario;
use Illuminate\Validation\ValidationException;

/**
 * Reglas del negocio de la recepción de una orden de compra.
 */
class RecepcionService
{
    /**
     * Registra una entrega (puede ser parcial) y marca la orden como Recibida cuando todo llegó.
     *
     * @param  array{fecha: string, observaciones?: string|null, items: array<int, array{orden_detalle_id: int, cantidad_recibida: float}>}  $datos
     */
    public function registrar(Orden $orden, array $datos, Usuario $responsable): Recepcion
    {
        if (! $orden->estaEnEstado(EstadoOrden::EMITIDA)) {
            throw ValidationException::withMessages(['orden' => 'Solo se pueden registrar recepciones de una orden emitida.']);
        }

        if ($datos['fecha'] < $orden->fecha_emision->toDateString()) {
            throw ValidationException::withMessages(['fecha' => 'La fecha de recepción no puede ser anterior a la emisión de la orden.']);
        }

        $this->validarItems($orden, $datos['items']);

        $recepcion = $orden->recepciones()->create([
            'responsable_id' => $responsable->id,
            'fecha' => $datos['fecha'],
            'observaciones' => $datos['observaciones'] ?? null,
        ]);

        foreach ($datos['items'] as $item) {
            $recepcion->detalles()->create([
                'orden_id' => $orden->id,
                'orden_detalle_id' => $item['orden_detalle_id'],
                'cantidad_recibida' => $item['cantidad_recibida'],
            ]);
        }

        $this->marcarRecibidaSiEstaCompleta($orden);

        return $recepcion;
    }

    /**
     * Cada ítem debe ser de la orden y no recibir más de lo que falta.
     */
    private function validarItems(Orden $orden, array $items): void
    {
        $detallesOrden = $orden->detalles()->with('recepcionDetalles')->get()->keyBy('id');

        foreach ($items as $posicion => $item) {
            $ordenDetalle = $detallesOrden->get($item['orden_detalle_id']);

            if (! $ordenDetalle) {
                throw ValidationException::withMessages([
                    "items.{$posicion}.orden_detalle_id" => 'El ítem no pertenece a la orden.',
                ]);
            }

            if ((float) $item['cantidad_recibida'] > $ordenDetalle->cantidad_pendiente) {
                throw ValidationException::withMessages([
                    "items.{$posicion}.cantidad_recibida" => "No puede recibir más de lo pendiente ({$ordenDetalle->cantidad_pendiente}).",
                ]);
            }
        }
    }

    private function marcarRecibidaSiEstaCompleta(Orden $orden): void
    {
        $todoRecibido = $orden->detalles()
            ->with('recepcionDetalles')
            ->get()
            ->every(fn (OrdenDetalle $ordenDetalle) => $ordenDetalle->cantidad_pendiente <= 0);

        if ($todoRecibido) {
            $orden->update(['estado_orden_id' => EstadoOrden::RECIBIDA]);
        }
    }
}
