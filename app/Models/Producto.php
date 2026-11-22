<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'tipo_producto_id',
        'nombre',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'tipo_producto_id' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function tipoProducto(): BelongsTo
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }
}
