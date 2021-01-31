<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Database;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class BootTest extends TestCase
{
    /**
     * @dataProvider databaseDriverDataProvider
     * @test
     * */
    public function calling_boot_will_set_the_database_config_database_to_the_current_hop_for_the_configured_database_driver($connection, $name, $databaseFile)
    {
        putenv("APP_KEY=1234");

        $database = is_callable($databaseFile) ? $databaseFile() : $databaseFile;

        $this->mock(Filer::class)->shouldReceive('currentHop')->andReturn('foobar');

        $this->mock(Engine::class)
            ->shouldReceive('current')
            ->andReturn(new Database($name, $database, $connection));

        Hop::boot();

        expect(config("database.connections.$connection.database"))->toEqual($database);
    }

    /** @test */
    public function the_config_will_not_be_overriden_if_the_environment_is_production()
    {
        putenv("APP_KEY=1234");
        Config::set('app.env', 'production');

        $this->mock(Engine::class)
            ->shouldNotReceive('current');

        Hop::boot();
    }

    /** @test */
    public function if_the_filer_returns_no_currentHop_the_connection_is_not_updated()
    {
        putenv("APP_KEY=1234");

        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->andReturn(null);

        $this->mock(Engine::class)
            ->shouldNotReceive('current');

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
