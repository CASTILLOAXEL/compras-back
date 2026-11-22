<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Direccion extends Model
{
    use SoftDeletes;

    protected $table = 'direcciones';

    protected $fillable = [
        'municipio_id',
        'aldea_id',
        'detalle',
    ];

    protected function casts(): array
    {
        return [
            'municipio_id' => 'integer',
            'aldea_id' => 'integer',
        ];
    }

    public function aldea(): BelongsTo
    {
        return $this->belongsTo(Aldea::class, 'aldea_id');
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }
}
