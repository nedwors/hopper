<?php

use Nedwors\Hopper\Connections\MySql;
use Nedwors\Hopper\Connections\Sqlite;
use Nedwors\Hopper\BootChecks\Environment;

return [
    'default-branch' => 'main',

    'connections' => [

        'sqlite' => [
            'driver' => Sqlite::class,
        ],

        'mysql' => [
            'driver' => MySql::class,
        ],

    ],

    'boot-checks' => [
        Environment::class
    ],

    'post-creation-steps' => [
        'migrate:fresh'
    ]
];
