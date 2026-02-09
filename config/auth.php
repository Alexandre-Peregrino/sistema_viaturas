<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'usuarios'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'usuarios',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [

        /*
         * Provider principal do sistema:
         * - carrega usuário local (App\Models\Usuario)
         * - valida credenciais via LDAP/AD (driver ldap_eloquent)
         */
        'usuarios' => [
            'driver' => 'ldap_eloquent',
            'model'  => App\Models\Usuario::class,
        ],

        /*
         * (Opcional) Se algum pacote ou parte do framework ainda referenciar "users",
         * mantemos um alias apontando para o mesmo provider, para evitar inconsistências.
         */
        'users' => [
            'driver' => 'ldap_eloquent',
            'model'  => App\Models\Usuario::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    | Como a autenticação é LDAP/AD, reset de senha normalmente não faz sentido
    | (a senha é do AD). Mantido apenas por compatibilidade.
    */
    'passwords' => [
        'usuarios' => [
            'provider' => 'usuarios',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        // Alias opcional para compatibilidade
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
