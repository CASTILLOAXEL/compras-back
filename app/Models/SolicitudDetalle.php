<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudDetalle extends Model
{
    use SoftDeletes;

    protected $table = 'solicitud_detalles';

    protected $fillable = [
        'solicitud_id',
        'producto_id',
        'unidad_medida_id',
        'cantidad',
        'precio_estimado',
    ];

    protected function casts(): array
    {
        return [
            'solicitud_id' => 'integer',
            'producto_id' => 'integer',
            'unidad_medida_id' => 'integer',
            'cantidad' => 'decimal:2',
            'precio_estimado' => 'decimal:2',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }
    protected function subtotal(): Attribute
    {
        return Attribute::get(fn () => round($this->cantidad * $this->precio_estimado, 2));
    }
}
