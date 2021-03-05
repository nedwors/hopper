<?php

namespace Nedwors\Hopper\Facades;

use Nedwors\Hopper\Database;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Facade;

/**
 * Class Hop
 * @package Nedwors\Hopper\Facades
 *
 * @method static void to(string $database) Hop to the given database
 * @method static void boot() Boot the current database for use by the application
 * @method static ?Database current() Returns the current hopper database connection if one exists
 * @method static bool delete(string $database) Deletes the database
 * @method static void handlePostCreation(?Command $command = null) Runs the post creation steps
 */
class Hop extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hopper';
    }
}
