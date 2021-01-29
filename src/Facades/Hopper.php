<?php

namespace Nedwors\Hopper\Facades;

use Illuminate\Support\Facades\Facade;

class Hopper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hopper';
    }
}
