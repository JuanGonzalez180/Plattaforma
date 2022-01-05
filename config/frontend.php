<?php

return [

    'base_url' => env('BASE_URL_FRONTEND', 'https://staging.plattaforma.com'),

    'endpoints' => [

        'authentication' => [
            'registerMember' => 'autenticacion/registrar-integrante',
            'login' => 'autenticacion/iniciar-sesion',
        ],

    ],

];
