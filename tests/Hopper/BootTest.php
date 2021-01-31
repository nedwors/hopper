<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Nedwors\Hopper\Database;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Contracts\Engine;
use Illuminate\Support\Facades\Config;

class BootTest extends TestCase
{
    /** @test */
    public function calling_boot_will_ask_the_engine_to_boot()
    {
        putenv("APP_KEY=1234");

        $this->mock(Engine::class)
            ->shouldReceive('boot')
            ->once();

        Hop::boot();
    }

    /** @test */
    public function the_config_will_not_be_overriden_if_the_environment_is_production()
    {
        putenv("APP_KEY=1234");
        $this->mock(Engine::class)->shouldNotReceive('boot');

        Config::partialMock()
            ->shouldReceive('get')
            ->withArgs(['app.env'])
            ->andReturn('production');

        Hop::boot();
    }

    /** @test */
    public function if_there_is_no_app_key_then_it_will_not_boot_as_we_are_not_inside_an_app()
    {
        putenv("APP_KEY=");

        $this->mock(Engine::class)
            ->shouldNotReceive('current');

        Hop::boot();
    }

    public function databaseDriverDataProvider()
    {
        return [
            ['sqlite', 'foobar', fn() => database_path('foobar.sqlite')]
        ];
    }
}
