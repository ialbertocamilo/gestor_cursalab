<?php

return [
    'database_store_enable' => env('ERROR_DATABASE_STORE_ENABLE', false),

    'status-colors' => [
        'pending' => 'red',
        'working' => 'orange',
        'solved' => '#5458ea',
        'stand-by' => '#767676',
        '' => '',
    ],

    'maintenance_message' => 'Estamos actualizando la plataforma, puedes volver a ingresar este Lunes desde las 10am.',
    'maintenance_subworkspace_message' => 'Estimado(a) usuario, estamos preparando la plataforma para una mejor experiencia. Puedes volver a ingresar en otro momento.'
];
