<?php

namespace Nedwors\Hopper\Tests;

use Nedwors\Hopper\HopperServiceProvider;
use Orchestra\Testbench;

abstract class TestCase extends Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            HopperServiceProvider::class
        ];
    }
}
