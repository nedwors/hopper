<?php

namespace Nedwors\Hopper\Tests;

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
