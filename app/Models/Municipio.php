<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipio extends Model
{
    use SoftDeletes;

    protected $table = 'municipios';

    protected $fillable = [
        'departamento_id',
        'nombre',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'departamento_id' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }
}
