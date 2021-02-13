<?php

use Nedwors\Hopper\BootChecks\AppKey;
use Nedwors\Hopper\BootChecks\Environment;

return [
    'default-branch' => 'main',

    'boot-checks' => [
        AppKey::class,
        Environment::class
    ]
];
