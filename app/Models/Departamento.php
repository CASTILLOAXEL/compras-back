<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use SoftDeletes;

    protected $table = 'departamentos';

    protected $fillable = [
        'pais_id',
        'nombre',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'pais_id' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }
}
