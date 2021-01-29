<?php

namespace Nedwors\Hopper\Tests;

use Orchestra\Testbench\TestCase;
use Nedwors\Hopper\HopperServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [HopperServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
