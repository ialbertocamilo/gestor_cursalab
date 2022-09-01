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
];
