<?php

use Nedwors\Hopper\Engines\SqliteEngine;

return [
    'default-database' => env('HOPPER_DEFAULT_DATABASE', 'database'),

    'default-branch' => 'main',

    'driver' => env('HOPPER_DRIVER', 'sqlite'),

    'drivers' => [
        'sqlite' => [
            'database-path' => 'hopper/',
            'engine' => SqliteEngine::class
        ]
    ]
];
