<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenDetalle extends Model
{
    use SoftDeletes;

    protected $table = 'orden_detalles';

    protected $fillable = [
        'orden_id',
        'solicitud_id',
        'solicitud_detalle_id',
        'cantidad',
        'precio_unitario',
    ];

    protected $casts = [
        'orden_id' => 'integer',
        'solicitud_id' => 'integer',
        'solicitud_detalle_id' => 'integer',
    ];

    // Estado de recepción de cada ítem (requerimiento 7): se calcula, no se guarda
    public const RECEPCION_PENDIENTE = 'Pendiente';
    public const RECEPCION_PARCIAL = 'Parcial';
    public const RECEPCION_COMPLETA = 'Completo';

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    // De aquí salen el producto y la unidad de medida
    public function solicitudDetalle(): BelongsTo
    {
        return $this->belongsTo(SolicitudDetalle::class, 'solicitud_detalle_id', 'id');
    }

    public function recepcionDetalles(): HasMany
    {
        return $this->hasMany(RecepcionDetalle::class, 'orden_detalle_id', 'id');
    }

    protected function subtotal(): Attribute
    {
        return Attribute::get(
            fn (): float => round((float) $this->cantidad * (float) $this->precio_unitario, 2)
        );
    }

    // Lo recibido en todas las entregas de este ítem
    protected function cantidadRecibida(): Attribute
    {
        return Attribute::get(
            fn (): float => round((float) $this->recepcionDetalles->sum('cantidad_recibida'), 2)
        );
    }

    protected function cantidadPendiente(): Attribute
    {
        return Attribute::get(
            fn (): float => max(round((float) $this->cantidad - $this->cantidad_recibida, 2), 0)
        );
    }

    protected function estadoRecepcion(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->cantidad_recibida <= 0) {
                return self::RECEPCION_PENDIENTE;
            }

            return $this->cantidad_pendiente > 0 ? self::RECEPCION_PARCIAL : self::RECEPCION_COMPLETA;
        });
    }
}
