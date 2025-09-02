<?php

return [
    // modo: 'teste' | 'producao'
    'mode'        => env('ROTA_MODE', 'teste'),

    // credenciais de login
    'user'        => env('ROTA_USER'),
    'pass'        => env('ROTA_PASS'),

    // parâmetros de rede/retry
    'timeout'     => (int) env('ROTA_TIMEOUT', 15),
    'retries'     => (int) env('ROTA_RETRIES', 3),
    'retry_sleep' => (int) env('ROTA_RETRY_SLEEP_MS', 200), // opcional (ms)

    // token/login
    'token_ttl'   => (int) env('ROTA_TOKEN_TTL', 3300), // ~55 min

    // cache das respostas (Repository)
    'cache_ttl'   => (int) env('ROTA_CACHE_SECONDS', 300), // 5 min

    // segurança do proxy (middleware)
    'api_key'     => env('ROTAWEB_API_KEY'),

    // parâmetros de negócio
    'orgao_id'    => (int) env('ROTA_ORGAO_ID', 1),

    // bases
    'base_urls' => [
        'producao' => 'https://rota.pm.rn.gov.br',
        'teste'    => 'https://treinamento.rota.pm.rn.gov.br',
    ],

    'verify' => filter_var(env('ROTA_VERIFY', true), FILTER_VALIDATE_BOOL),

    'endpoints' => [
        'veiculos' => '/api/veiculos',
        // no futuro: 'unidades' => '/api/unidades', etc.
    ],

];
