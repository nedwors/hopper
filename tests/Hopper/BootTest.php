<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class BootTest extends TestCase
{
    /**
     * @dataProvider databaseDriverDataProvider
     * @test
     * */
    public function calling_boot_will_set_the_database_config_database_to_the_current_hop_for_the_configured_database_driver($driver, $database)
    {
        putenv("APP_KEY=1234");

        $database = is_callable($database) ? $database() : $database;

        $this->mock(Filer::class)->shouldReceive('currentHop')->andReturn('foobar');

        $this->mock(Engine::class)
            ->shouldReceive('normalize')
            ->andReturn($database)
            ->shouldReceive('connection')
            ->andReturn($driver);

        Hop::boot();

        expect(config("database.connections.$driver.database"))->toEqual($database);
    }

    /**
     * @dataProvider databaseDriverDataProvider
     * @test
     * */
    public function calling_boot_will_purge_the_DB_facade($driver, $database)
    {
        $database = is_callable($database) ? $database() : $database;

        $this->mock(Filer::class)->shouldReceive('currentHop')->andReturn('foobar');

        $this->mock(Engine::class)
            ->shouldReceive('normalize')
            ->andReturn($database)
            ->shouldReceive('connection')
            ->andReturn($driver);

        DB::partialMock()
            ->shouldReceive('purge')
            ->once();

        Hop::boot();
    }

    /** @test */
    public function the_config_will_not_be_overriden_and_the_db_not_purged_if_the_environment_is_production()
    {
        Config::set('app.env', 'production');

        $this->mock(Engine::class)
            ->shouldNotReceive('normalize')
            ->shouldNotReceive('connection');

        DB::partialMock()
            ->shouldNotReceive('purge');

        Hop::boot();
    }

    /** @test */
    public function if_the_filer_returns_no_currentHop_the_DB_is_not_purged_and_the_connection_not_updated()
    {
        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->andReturn(null);

        $this->mock(Engine::class)
            ->shouldNotReceive('normalize')
            ->shouldNotReceive('connection');

        Hop::boot();
    }

    /** @test */
    public function if_there_is_no_app_key_then_it_will_not_boot_as_we_are_not_inside_an_app()
    {
        putenv("APP_KEY=");

        $this->mock(Engine::class)
            ->shouldNotReceive('normalize')
            ->shouldNotReceive('connection');

        DB::partialMock()
            ->shouldNotReceive('purge');

        Hop::boot();
    }

    public function databaseDriverDataProvider()
    {
        return [
            ['sqlite', fn() => database_path('foobar.sqlite')]
        ];
    }
}
