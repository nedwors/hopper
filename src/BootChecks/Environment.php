<?php

namespace Nedwors\Hopper\BootChecks;

use Illuminate\Support\Facades\App;
use Nedwors\Hopper\Contracts\BootCheck;

class Environment implements BootCheck
{
    public function check(): bool
    {
        return config('app.env') == 'local';
    }
}
