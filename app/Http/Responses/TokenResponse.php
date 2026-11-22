<?php

namespace App\Http\Responses;

use App\Models\Usuario;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class TokenResponse implements Responsable
{
    private const MINUTOS_EXPIRACION = 350;

    public function __construct(private readonly Usuario $usuario)
    {
    }

    public function toResponse($request): JsonResponse
    {
        $permisos = $this->usuario->getAllPermissions()->pluck('name')->all();

        $token = $this->usuario->createToken('App', $permisos, now()->addMinutes(self::MINUTOS_EXPIRACION));

        return response()->json([
            'token' => $token->plainTextToken,
            'expiracion' => $token->accessToken->expires_at,
            'permissions' => $token->accessToken->abilities,
        ]);
    }
}
