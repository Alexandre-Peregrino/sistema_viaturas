<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Alias de middleware de rota
        $middleware->alias([
            'auth_or_debug'      => \App\Http\Middleware\AuthOrDebugToken::class,
            'cadastro_completo'  => \App\Http\Middleware\EnsureCadastroCompleto::class,
            'permitido'          => \App\Http\Middleware\EnsurePermitido::class,
        ]);

        /**
         * Isenta CSRF apenas para o probe.
         *
         * IMPORTANTE:
         * Não use app()->environment() aqui, porque nessa fase o binding "env"
         * ainda pode não estar pronto no container, gerando:
         * "Target class [env] does not exist".
         *
         * Portanto, checamos o ambiente via variáveis de processo.
         */
        $appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';

        if ($appEnv === 'local') {
            $middleware->validateCsrfTokens(except: [
                'admin/viaturas/rota/probe',
            ]);
        }

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
