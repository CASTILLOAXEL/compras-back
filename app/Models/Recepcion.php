<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recepcion extends Model
{
    use SoftDeletes;

    protected $table = 'recepciones';

    protected $fillable = ['orden_id', 'responsable_id', 'fecha', 'observaciones'];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'orden_id' => 'integer',
        'responsable_id' => 'integer',
    ];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'orden_id', 'id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsable_id', 'id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(RecepcionDetalle::class, 'recepcion_id', 'id');
    }
}
