<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoContrato extends Model
{
    use SoftDeletes;

    public const VIGENTE = 1;
    public const FINALIZADO = 2;
    public const ANULADO = 3;

    protected $table = 'estados_contrato';

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
