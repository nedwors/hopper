<?php

namespace Nedwors\Hopper\Facades;

use Illuminate\Support\Facades\Facade;
use Nedwors\Hopper\Database;

/**
 * Class Hop
 * @package Nedwors\Hopper\Facades
 *
 * @method static void to(string $database) Hop to the given database
 * @method static void boot() Boot the current database for use by the application
 * @method static ?Database current() Returns the current hopper database connection if one exists
 * @method static bool delete(string $database) Deletes the database
 */
class Hop extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hopper';
    }
}
