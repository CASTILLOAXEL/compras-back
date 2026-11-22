<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'razon_social',
        'nit',
        'dpi',
        'persona_contacto',
        'telefono',
        'correo',
        'direccion_id',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'direccion_id' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }
}
