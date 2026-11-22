<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orden extends Model
{
    use SoftDeletes;

    protected $table = 'ordenes';

    // numero, fecha y estado los asigna OrdenService; el usuario no los envía
    protected $fillable = [
        'numero',
        'fecha_emision',
        'solicitud_id',
        'proveedor_id',
        'estado_orden_id',
    ];

    protected $casts = [
        'fecha_emision' => 'date:Y-m-d',
        'solicitud_id' => 'integer',
        'proveedor_id' => 'integer',
        'estado_orden_id' => 'integer',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id', 'id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'id');
    }

    public function estadoOrden(): BelongsTo
    {
        return $this->belongsTo(EstadoOrden::class, 'estado_orden_id', 'id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenDetalle::class, 'orden_id', 'id');
    }

    public function recepciones(): HasMany
    {
        return $this->hasMany(Recepcion::class, 'orden_id', 'id');
    }

    public function estaEnEstado(int $estadoOrdenId): bool
    {
        return $this->estado_orden_id === $estadoOrdenId;
    }

    /**
     * Dato calculado (no se guarda): suma de los subtotales de sus ítems.
     */
    protected function total(): Attribute
    {
        return Attribute::get(
            fn (): float => round($this->detalles->sum(fn (OrdenDetalle $ordenDetalle) => $ordenDetalle->subtotal), 2)
        );
    }
}
