<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrato extends Model
{
    use SoftDeletes;

    protected $table = 'contratos';

    protected $fillable = [
        'proveedor_id',
        'tipo_producto_id',
        'estado_contrato_id',
        'fecha_inicio',
        'fecha_fin',
        'monto',
    ];

    protected function casts(): array
    {
        return [
            'proveedor_id' => 'integer',
            'tipo_producto_id' => 'integer',
            'estado_contrato_id' => 'integer',
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
            'monto' => 'decimal:2',
        ];
    }

    public function estadoContrato(): BelongsTo
    {
        return $this->belongsTo(EstadoContrato::class, 'estado_contrato_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function tipoProducto(): BelongsTo
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }
}
