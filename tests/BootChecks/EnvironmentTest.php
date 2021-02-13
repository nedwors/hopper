<?php

namespace Nedwors\Hopper\Tests\BootChecks;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\BootChecks\Environment;
use Nedwors\Hopper\Tests\TestCase;

class EnvironmentTest extends TestCase
{
    /** @test */
    public function if_the_environment_is_local_it_returns_true()
    {
        Config::set('app.env', 'local');

        $check = app(Environment::class);

        expect($check->check())->toBeTrue();
    }

    /**
     * @dataProvider nonLocalEnvironmentsDataProvider
     * @test */
    public function if_the_environment_is_not_local_it_returns_false($environment)
    {
        Config::set('app.env', $environment);

        $check = app(Environment::class);

        expect($check->check())->toBeFalse();
    }

    public function nonLocalEnvironmentsDataProvider()
    {
        return [
            ['staging'],
            ['development'],
            ['production']
        ];
    }
}
