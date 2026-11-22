<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Symfony\Component\HttpFoundation\Response;

/**
 * el Super Administrador pasa sin validar permisos;
 * los demás usuarios necesitan el permiso en su token.
 */
class CustomCheckAbilities
{
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        if ($request->user()?->isSuperAdmin()) {
            return $next($request);
        }

        return app(CheckAbilities::class)->handle($request, $next, ...$abilities);
    }
}
