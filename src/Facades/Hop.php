<?php

namespace Nedwors\Hopper\Facades;

use Illuminate\Support\Facades\Facade;

class Hop extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hopper';
    }
}
