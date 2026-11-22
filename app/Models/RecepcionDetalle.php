<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecepcionDetalle extends Model
{
    use SoftDeletes;

    protected $table = 'recepcion_detalles';

    protected $fillable = ['recepcion_id', 'orden_id', 'orden_detalle_id', 'cantidad_recibida'];

    protected $casts = [
        'recepcion_id' => 'integer',
        'orden_id' => 'integer',
        'orden_detalle_id' => 'integer',
    ];

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'recepcion_id', 'id');
    }

    public function ordenDetalle(): BelongsTo
    {
        return $this->belongsTo(OrdenDetalle::class, 'orden_detalle_id', 'id');
    }
}
