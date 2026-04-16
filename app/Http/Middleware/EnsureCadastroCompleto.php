<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCadastroCompleto
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Rotas que não podem causar loop / ou que podem ser vistas mesmo sem cadastro completo
        $allowedRoutes = [
            'home',
            'funcionalidades',
            'home.restrita',
            'perfil.completar',
            'perfil.completar.store',
            'logout',
        ];

        if ($request->route() && in_array($request->route()->getName(), $allowedRoutes, true)) {
            return $next($request);
        }

        if (!$user->cadastro_completo) {
            return redirect()->route('perfil.completar');
        }

        return $next($request);
    }
}
