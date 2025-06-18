<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Linhas de Linguagem de Validação
    |--------------------------------------------------------------------------
    |
    | As seguintes linhas contêm as mensagens de erro padrão usadas pela
    | validação do Laravel. Você pode modificar essas mensagens conforme
    | precisar para tornar sua aplicação mais amigável.
    |
    */

    'accepted' => 'O campo :attribute precisa ser aceito.',
    'active_url' => 'O campo :attribute não é uma URL válida.',
    'after' => 'O campo :attribute deve ser uma data posterior a :date.',
    'alpha' => 'O campo :attribute deve conter apenas letras.',

    // Mensagem geral para unique
    'unique' => 'O :attribute informado já está em uso.',

    /*
    |--------------------------------------------------------------------------
    | Mensagens Personalizadas para Campos Específicos
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'cpf' => [
            'unique' => 'CPF já cadastrado',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atributos Personalizados
    |--------------------------------------------------------------------------
    |
    | Estes valores trocam nomes de atributos por nomes mais amigáveis.
    |
    */

    'attributes' => [
        'cpf' => 'CPF',
        'email' => 'e-mail',
        'password' => 'senha',
    ],

];
