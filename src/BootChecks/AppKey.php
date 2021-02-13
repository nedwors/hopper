<?php

namespace Nedwors\Hopper\BootChecks;

use Nedwors\Hopper\Contracts\BootCheck;

class AppKey implements BootCheck
{
    public function check(): bool
    {
        return config('app.key') ? true : false;
    }
}
