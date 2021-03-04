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
}
