<?php

namespace Nedwors\Hopper\Tests;

use Illuminate\Support\Facades\Event;
use Nedwors\Hopper\HopperServiceProvider;
use Orchestra\Testbench;

abstract class TestCase extends Testbench\TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app->config->set('database.default', 'sqlite');
    }

    protected function getPackageProviders($app)
    {
        return [
            HopperServiceProvider::class
        ];
    }
}
