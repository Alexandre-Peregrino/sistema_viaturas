<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermitido
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Rotas permitidas mesmo sem aprovação (evitar loop)
        $allowedRoutes = [
            'home',
            'home.restrita',
            'perfil.completar',
            'perfil.completar.store',
            'logout',
        ];

        if ($request->route() && in_array($request->route()->getName(), $allowedRoutes, true)) {
            return $next($request);
        }

        // Se não foi aprovado ainda, manda pra home restrita
        if (!$user->permitido) {
            return redirect()->route('home.restrita')
                ->with('error', 'Seu acesso ainda não foi liberado por um administrador.');
        }

        return $next($request);
    }
}
