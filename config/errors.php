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
    'maintenance_subworkspace_message' => 'Estimado(a) usuario, estamos preparando la plataforma para una mejor experiencia. Puedes volver a ingresar en otro momento.',
    'maintenance_ucfp' => 'Estimado(a) usuario, estamos preparando tu nueva plataforma de capacitación, podrás ingresar desde el 02/11 a las 10:00am.',

    'limit-errors' => [
        'limit-user-allowed' => 'Has superado el límite de usuarios activos.',
        'limit-storage-allowed' => 'Has superado la capacidad de almacenamiento dentro de la plataforma.'
    ]
];
