<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevBypassAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Segurança: nunca permitir isso fora do ambiente local
        if (!app()->environment('local')) {
            return $next($request);
        }

        // Liga/Desliga via .env
        if (!filter_var(env('DEV_BYPASS_AUTH', false), FILTER_VALIDATE_BOOL)) {
            return $next($request);
        }

        // Se já está autenticado, segue normal
        if (Auth::check()) {
            return $next($request);
        }

        // Tenta por ID
        $id = (int) env('DEV_BYPASS_USER_ID', 0);
        $user = $id > 0 ? Usuario::find($id) : null;

        // Tenta por CPF (funciona mesmo se o CPF no banco tiver máscara)
        if (!$user) {
            $cpf = (string) env('DEV_BYPASS_CPF', '');
            $cpfDigits = preg_replace('/\D+/', '', $cpf);

            if ($cpfDigits !== '') {
                // PostgreSQL: regexp_replace
                $user = Usuario::query()
                    ->whereRaw("regexp_replace(cpf, '\\D', '', 'g') = ?", [$cpfDigits])
                    ->first();
            }
        }

        // Fallback: primeiro usuário
        if (!$user) {
            $user = Usuario::query()->orderBy('id')->first();
        }

        // Se não existe ninguém no banco, não tem como simular login
        if (!$user) {
            abort(500, 'DEV_BYPASS_AUTH ligado, mas não existe nenhum usuário na tabela usuarios.');
        }

        Auth::login($user);

        return $next($request);
    }
}
