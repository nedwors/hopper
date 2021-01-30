<?php

namespace Nedwors\Hopper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Git
 * @package Nedwors\Hopper\Facades
 *
 * @method static ?string current() The current git branch name
 */
class Git extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hopper-git';
    }
}
