<?php

namespace Nedwors\Hopper\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * Class Hop
 * @package Nedwors\Hopper\Facades
 *
 * @method static void to(string $database) Hop to the given database
 * @method static void boot() Boot the current database for use by the application
 * @method static ?string current() Returns the name of the current hopper database connection if one exists
 */
class Hop extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hopper';
    }
}