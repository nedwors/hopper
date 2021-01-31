<?php

return [
    'default-database' => env('HOPPER_DEFAULT_DATABASE', 'database'),

    'default-branch' => 'main',

    'connection' => env('HOPPER_CONNECTION', 'sqlite'),

    'connections' => [
        'sqlite' => [
            'database-path' => 'hopper/'
        ]
    ]
];
