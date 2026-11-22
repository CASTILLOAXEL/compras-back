<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Solicitud extends Model
{
    use SoftDeletes;

    protected $table = 'solicitudes';

    protected $fillable = [
        'numero',
        'fecha',
        'solicitante_id',
        'centro_costo_id',
        'estado_solicitud_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
            'solicitante_id' => 'integer',
            'centro_costo_id' => 'integer',
            'estado_solicitud_id' => 'integer',
        ];
    }

    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class, 'centro_costo_id');
    }

    public function estadoSolicitud(): BelongsTo
    {
        return $this->belongsTo(EstadoSolicitud::class, 'estado_solicitud_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'solicitante_id');
    }
}
