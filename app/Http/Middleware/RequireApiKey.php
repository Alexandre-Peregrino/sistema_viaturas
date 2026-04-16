<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('rotaweb.api_key');
        $got = $request->header('X-API-Key');

        if (!$expected || $got !== $expected) {
            return response()->json(['message' => 'API key inválida'], 401);
        }

        return $next($request);
    }
}
