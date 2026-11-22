<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoSolicitud extends Model
{
    use SoftDeletes;

    protected $table = 'estados_solicitud';

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
