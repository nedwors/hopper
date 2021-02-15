<?php

return [
    'default-branch' => 'main',

    'connections' => [

        'sqlite' => [
            'driver' => Nedwors\Hopper\Connections\Sqlite::class,
        ],

        'mysql' => [
            'driver' => Nedwors\Hopper\Connections\MySql::class,
        ],

    ],
];
