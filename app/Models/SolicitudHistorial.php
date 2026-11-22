<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class SolicitudHistorial extends Model
{
    use SoftDeletes;

    protected $table = 'solicitud_historial';

    protected $fillable = [
        'solicitud_id',
        'estado_solicitud_id',
        'usuario_id',
        'observacion',
    ];

    public function estadoSolicitud(): BelongsTo
    {
        return $this->belongsTo(EstadoSolicitud::class, 'estado_solicitud_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
