<?php
namespace Nedwors\Hopper\Tests\Engines\Engine;

use Nedwors\Hopper\Contracts;
use Nedwors\Hopper\Engines\Engine;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Filer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Events\HoppedToDefault;
use Nedwors\Hopper\Events\HoppedToDatabase;
use Nedwors\Hopper\Exceptions\NoConnectionException;

class UseTest extends TestCase
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
    public function use_will_ask_the_connection_to_create_a_new_database_if_it_does_not_exist($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('sanitize')
            ->andReturn($name)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->once()
            ->andReturn(false)
            ->shouldReceive('create')
            ->once()
            ->withArgs([$name]);

        app(Engine::class)->use($name);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function calling_use_with_the_default_option_will_use_the_default_database($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldNotReceive('create');

        $this->mock(Filer::class)
            ->shouldReceive('flushCurrentHop')
            ->once();

        app(Engine::class)->use(Contracts\Engine::DEFAULT);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function use_will_not_ask_the_connection_to_create_a_new_database_if_it_exists($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('sanitize')
            ->andReturn($name)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->once()
            ->andReturn(true)
            ->shouldNotReceive('create');

        app(Engine::class)->use('foobar');
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function use_will_file_the_currentHop_by_its_name_if_the_database_is_created_by_the_connection($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('sanitize')
            ->andReturn($name)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->andReturn(false)
            ->shouldReceive('create')
            ->withArgs([$name]);

        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop')
            ->once()
            ->withArgs([$name]);

        app(Engine::class)->use($name);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function use_will_file_the_currentHop_even_if_the_database_is_not_created($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('sanitize')
            ->andReturn($name)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldNotReceive('create');

        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop')
            ->once()
            ->withArgs([$name]);

        app(Engine::class)->use($name);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function when_a_hop_is_filed_a_HoppedDatabase_event_is_fired($connection, $name, $database, $default)
    {
        Event::assertNotDispatched(HoppedToDatabase::class);

        $this->mock(Connection::class)
            ->shouldReceive('sanitize')
            ->andReturn($name)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->andReturn(true);

        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop')
            ->once()
            ->withArgs([$name]);

        app(Engine::class)->use($name);

        Event::assertDispatched(HoppedToDatabase::class, function ($event) use ($name) {
            $this->assertEquals($name, $event->name);
            return true;
        });
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function calling_use_with_the_configured_default_database_name_will_not_create_a_database_and_will_flush_the_currentHop($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldNotReceive('create');

        $this->mock(Filer::class)
            ->shouldReceive('flushCurrentHop')
            ->once();

        app(Engine::class)->use($default);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function calling_use_with_the_configured_default_git_branch_will_function_the_same_as_calling_use_with_the_default_database($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);
        Config::set('hopper.default-branch', 'staging');

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldNotReceive('create');

        $this->mock(Filer::class)
            ->shouldReceive('flushCurrentHop')
            ->once();

        app(Engine::class)->use('staging');
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function when_the_current_hop_is_flushed_a_HoppedToDefault_event_is_fired($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        Event::assertNotDispatched(HoppedToDefault::class);

        $this->mock(Connection::class)
            ->shouldReceive('sanitize')
            ->andReturn($name)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldNotReceive('create');

        $this->mock(Filer::class)
            ->shouldReceive('flushCurrentHop')
            ->once();

        app(Engine::class)->use($default);

        Event::assertDispatched(HoppedToDefault::class);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function when_a_database_is_created_an_DatabaseCreated_event_is_fired($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        Event::assertNotDispatched(DatabaseCreated::class);

        $this->mock(Connection::class)
            ->shouldReceive('sanitize')
            ->andReturn($name)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->andReturn(false)
            ->shouldReceive('create')
            ->once()
            ->withArgs([$name]);

        app(Engine::class)->use($name);

        Event::assertDispatched(DatabaseCreated::class, fn($event) => $event->name == $name);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function when_a_database_is_not_created_an_DatabaseCreated_event_is_not_fired($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        Event::assertNotDispatched(DatabaseCreated::class);

        $this->mock(Connection::class)
            ->shouldReceive('sanitize')
            ->andReturn($name)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldNotReceive('create');

        app(Engine::class)->use($name);

        Event::assertNotDispatched(DatabaseCreated::class);
    }

   /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function when_calling_use_if_the_connection_is_not_resolvable_a_NoConnectionException_is_thrown($connection, $name, $database)
    {
        $this->app->bind(Connection::class, fn() => null);

        $this->expectException(NoConnectionException::class);

        app(Engine::class)->use($name);
    }
}
