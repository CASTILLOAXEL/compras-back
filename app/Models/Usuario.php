<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasRoles, SoftDeletes;

    protected $table = 'usuarios';


    protected $guard_name = 'api';

    protected $fillable = [
        'dpi',
        'primer_nombre',
        'segundo_nombre',
        'tercer_nombre',
        'primer_apellido',
        'segundo_apellido',
        'tercer_apellido',
        'correo',
        'password',
        'direccion_id',
        'estado',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'estado' => 'boolean',
        ];
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => collect([
            $this->primer_nombre,
            $this->segundo_nombre,
            $this->tercer_nombre,
            $this->primer_apellido,
            $this->segundo_apellido,
            $this->tercer_apellido,
        ])->filter()->implode(' '));
    }


    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMINISTRADOR);
    }


    public function abilityRules(): array
    {
        return $this->getAllPermissions()
            ->map(fn (Permission $permiso) => [
                'action' => $permiso->name,
                'subject' => $permiso->subject,
            ])
            ->values()
            ->all();
    }


    public function userResponse(): array
    {
        return [
            ...$this->toArray(),
            'nombre_completo' => $this->nombre_completo,
            'roles' => $this->getRoleNames(),
            'abilityRules' => $this->abilityRules(),
        ];
    }
}
