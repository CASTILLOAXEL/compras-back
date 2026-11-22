<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoOrden extends Model
{
    use SoftDeletes;

    protected $table = 'estados_orden';

    // NUEVO: mismos ids que EstadoOrdenSeeder (Emitida = 1 ...)
    public const EMITIDA = 1;
    public const RECIBIDA = 2;
    public const CANCELADA = 3;

    protected $fillable = [
        'nombre',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }
}
