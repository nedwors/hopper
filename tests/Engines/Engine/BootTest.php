<?php
namespace Nedwors\Hopper\Tests\Engines\Engine;

use Nedwors\Hopper\Database;
use Nedwors\Hopper\Contracts;
use Nedwors\Hopper\Engines\Engine;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Filer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Events\DatabaseDeleted;
use Nedwors\Hopper\Events\HoppedToDefault;
use Nedwors\Hopper\Events\HoppedToDatabase;
use Nedwors\Hopper\Events\DatabaseNotDeleted;
use Nedwors\Hopper\Exceptions\NoConnectionException;

class BootTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop')
            ->shouldReceive('flushCurrentHop');

        Event::fake();
    }

   /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function calling_boot_will_set_the_database_config_database_to_the_current_hop_for_the_configured_database_driver($connection, $name, $database)
    {
        $database = value($database);

        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->once()
            ->andReturn('foobar');

        $this->mock(Connection::class)
            ->shouldReceive('database')
            ->once()
            ->withArgs([$name])
            ->andReturn($database)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('boot');

        app(Engine::class)->boot();

        expect(config("database.connections.$connection.database"))->toEqual($database);
    }

   /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function when_the_engine_boots_it_asks_the_connection_to_boot($connection, $name, $database)
    {
        $database = value($database);

        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->andReturn('foobar');

        $this->mock(Connection::class)
            ->shouldReceive('database')
            ->andReturn($database)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('boot')
            ->once();

        app(Engine::class)->boot();
    }
}
