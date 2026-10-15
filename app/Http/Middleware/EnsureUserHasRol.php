<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRol
{
    /**
     * Restringe el acceso según el/los rol(es) permitido(s).
     *
     * Uso: ->middleware('rol:ADMIN') o 'rol:ADMIN,JURADO'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        abort_unless($usuario !== null, Response::HTTP_UNAUTHORIZED);
        abort_unless(in_array($usuario->rol?->value, $roles, true), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}