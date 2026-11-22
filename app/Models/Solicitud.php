<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'solicitante_id');
    }

    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class, 'centro_costo_id');
    }

    public function estadoSolicitud(): BelongsTo
    {
        return $this->belongsTo(EstadoSolicitud::class, 'estado_solicitud_id');
    }

    public function orden(): HasOne
    {
        return $this->hasOne(Orden::class, 'solicitud_id', 'id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(SolicitudDetalle::class, 'solicitud_id');
    }
    public function historial(): HasMany
    {
        return $this->hasMany(SolicitudHistorial::class, 'solicitud_id')->latest('id');
    }


    public function estaEnEstado(int $estadoSolicitudId): bool
    {
        return $this->estado_solicitud_id === $estadoSolicitudId;
    }


    protected function totalEstimado(): Attribute
    {
        return Attribute::get(fn () => round(
            $this->detalles->sum(fn (SolicitudDetalle $detalle) => $detalle->cantidad * $detalle->precio_estimado),
            2
        ));
    }

    public function scopeVisiblesPara(Builder $consulta, Usuario $usuario): Builder
    {
        if ($usuario->isSuperAdmin() || $usuario->checkPermissionTo('aprobar solicitudes')) {
            return $consulta;
        }

        return $consulta->where('solicitante_id', $usuario->id);
    }
}
