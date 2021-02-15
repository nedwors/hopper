<?php

use Nedwors\Hopper\BootChecks\Environment;

return [
    'default-branch' => 'main',

    'boot-checks' => [
        Environment::class
    ]
];
