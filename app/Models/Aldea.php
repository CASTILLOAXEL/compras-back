<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aldea extends Model
{
    use SoftDeletes;

    protected $table = 'aldeas';

    protected $fillable = [
        'municipio_id',
        'nombre',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'municipio_id' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }
}
