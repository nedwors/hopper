<?php

namespace Nedwors\Hopper\Tests;

use Orchestra\Testbench;
use Nedwors\Hopper\HopperServiceProvider;

abstract class TestCase extends Testbench\TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->config->set('database.default', 'sqlite');
    }

    protected function getPackageProviders($app)
    {
        return [
            HopperServiceProvider::class
        ];
    }

    public function databaseConnectionDataProvider()
    {
        return [
            ['sqlite', 'foobar', fn() => database_path('foobar.sqlite'), 'database'],
            ['mysql', 'foobar', 'hopper_foobar', 'hopper'],
        ];
    }
}
