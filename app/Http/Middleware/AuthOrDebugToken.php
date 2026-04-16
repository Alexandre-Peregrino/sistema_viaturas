<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthOrDebugToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1) Se está autenticado, passa.
        if (auth()->check()) {
            return $next($request);
        }

        // 2) Se veio debug token (query ou header), valida.
        $expected = (string) config('app_debug.debug_token');
        $given = (string) (
            $request->query('debug_token')
            ?? $request->header('X-Debug-Token')
            ?? ''
        );

        if ($expected !== '' && $given !== '' && hash_equals($expected, $given)) {
            // Recomendação: limitar uso do debug token ao ambiente local
            if (!app()->isLocal()) {
                return $this->deny($request);
            }
            return $next($request);
        }

        return $this->deny($request);
    }

    private function deny(Request $request): Response
    {
        // Se o cliente pede JSON, devolve 401 em JSON (melhor para curl)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Caso contrário, comportamento padrão web
        return redirect()->route('login');
    }
}
