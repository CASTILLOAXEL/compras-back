<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoSolicitud extends Model
{
    use SoftDeletes;

     const BORRADOR = 1;
     const EN_APROBACION = 2;
     const APROBADA = 3;
     const RECHAZADA = 4;
     const COMPLETADA = 5;

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
