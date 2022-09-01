<?php

return [
    'notification_enable' => env('SLACK_NOTIFICATION_ENABLE', false),
    'routes' => [
    	'posts' => env('SLACK_ROUTE_POSTS', 'NOT_FOUND'),
        'errors' => env('SLACK_ROUTE_ERROR', 'NOT_FOUND'),
        'general' => env('SLACK_ROUTE_GENERAL', 'NOT_FOUND'),
        'support' => env('SLACK_ROUTE_SUPPORT', 'NOT_FOUND'),
    ]
];
