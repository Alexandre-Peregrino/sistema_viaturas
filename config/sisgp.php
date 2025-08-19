<?php

return [
    'mode'     => env('SISGP_MODE', env('MODE', 'test')), // test | producao
    'sistema'  => env('SISGP_SISTEMA', 'ROTAWEB'),
    'semente'  => env('SISGP_SEMENTE'),
    'user_cpf' => env('SISGP_USER'),

    'urls' => [
        'test'     => rtrim(env('SISGP_URL_TESTE', ''), '/'),
        'producao' => rtrim(env('SISGP_URL_PROD', ''), '/'),
    ],
];
