<?php

namespace Nedwors\Hopper\BootChecks;

use Nedwors\Hopper\Contracts\BootCheck;

class Environment implements BootCheck
{
    public function check(): bool
    {
        return config('app.env') == 'local';
    }
}
