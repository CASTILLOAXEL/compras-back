<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\TokenResponse;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends AppBaseController
{
    /**
     * POST api/auth/login
     */
    public function login(LoginRequest $request): TokenResponse
    {
        $usuario = Usuario::where('correo', $request->correo)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'message' => ['Correo o contraseña incorrectos.'],
            ]);
        }

        if (! $usuario->estado) {
            throw ValidationException::withMessages([
                'message' => ['El usuario está inactivo, comuníquese con el administrador.'],
            ]);
        }

        return new TokenResponse($usuario);
    }

    /**
     * POST api/auth/logout  (elimina solo el token actual)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendSuccess('Sesión cerrada.');
    }

    /**
     * GET api/user  (lo consume el front al iniciar sesión)
     */
    public function usuario(Request $request): JsonResponse
    {
        return response()->json($request->user()->userResponse());
    }
}
