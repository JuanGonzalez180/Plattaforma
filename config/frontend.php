<?php

return [

    'base_url' => env('BASE_URL_FRONTEND', 'http://platt.incdustry.com'),

    'endpoints' => [

        'authentication' => [
            'registerMember' => 'autenticacion/registrar-integrante',
            'login' => 'autenticacion/iniciar-sesion',
        ],

    ],

];
