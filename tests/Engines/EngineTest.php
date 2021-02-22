<?php
namespace Nedwors\Hopper\Tests\Engines;

use Nedwors\Hopper\Database;
use Nedwors\Hopper\Engines\Engine;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Filer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Contracts;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Events\DatabaseDeleted;
use Nedwors\Hopper\Events\HoppedToDefault;
use Nedwors\Hopper\Events\HoppedToDatabase;
use Nedwors\Hopper\Events\DatabaseNotDeleted;
use Nedwors\Hopper\Exceptions\NoConnectionException;

class EngineTest extends TestCase
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
    public function delete_will_ask_the_connection_to_delete_the_given_database_if_it_exists($connection, $name, $database, $default)
    {
        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->once()
            ->withArgs([$name])
            ->andReturn(true)
            ->shouldReceive('delete')
            ->once()
            ->withArgs([$name])
            ->andReturn(true);

        app(Engine::class)->delete($name);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function delete_will_hop_to_the_default_database_post_delete($connection, $name, $database, $default)
    {
        $this->mock(Filer::class)
            ->shouldReceive('flushCurrentHop')
            ->once();

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->once()
            ->withArgs([$name])
            ->andReturn(true)
            ->shouldReceive('delete')
            ->once()
            ->withArgs([$name])
            ->andReturn(true);

        app(Engine::class)->delete($name);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function delete_will_not_ask_the_connection_to_delete_the_given_database_if_it_doesnt_exist($connection, $name, $database, $default)
    {
        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->once()
            ->withArgs([$name])
            ->andReturn(false)
            ->shouldNotReceive('delete');

        app(Engine::class)->delete($name);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function if_the_database_given_is_the_default_database_the_connection_is_not_asked_to_delete_it($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldNotReceive('delete');

        app(Engine::class)->delete($default);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function calling_delete_with_the_configured_default_git_branch_will_function_the_same_as_calling_delete_with_the_default_database($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);
        Config::set('hopper.default-branch', 'staging');

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldNotReceive('delete');

        app(Engine::class)->delete('staging');
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function a_DatabaseDeleted_event_is_dispatched_when_a_database_is_deleted($connection, $name)
    {
        Event::assertNotDispatched(DatabaseDeleted::class);

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->withArgs([$name])
            ->andReturn(true)
            ->shouldReceive('delete')
            ->once()
            ->withArgs([$name])
            ->andReturn(true);

        app(Engine::class)->delete($name);

        Event::assertDispatched(DatabaseDeleted::class, fn($event) => $event->name == $name);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function a_DatabaseDeleted_event_is_not_dispatched_when_a_database_is_not_deleted($connection, $name)
    {
        Event::assertNotDispatched(DatabaseDeleted::class);

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->withArgs([$name])
            ->andReturn(false)
            ->shouldNotReceive('delete');

        app(Engine::class)->delete($name);

        Event::assertNotDispatched(DatabaseDeleted::class);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function a_DatabaseNotDeleted_event_is_dispatched_when_a_database_is_not_deleted_if_the_database_doesnt_exist($connection, $name)
    {
        Event::assertNotDispatched(DatabaseNotDeleted::class);

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->withArgs([$name])
            ->andReturn(false)
            ->shouldNotReceive('delete');

        app(Engine::class)->delete($name);

        Event::assertDispatched(DatabaseNotDeleted::class, function ($event) use ($name) {
            $this->assertEquals($name, $event->name);
            $this->assertEquals(DatabaseNotDeleted::DOES_NOT_EXIST, $event->reason);
            return true;
        });
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function a_DatabaseNotDeleted_event_is_dispatched_when_a_database_is_not_deleted_if_the_database_is_the_default_database($connection, $name, $database, $default)
    {
        Event::assertNotDispatched(DatabaseNotDeleted::class);

        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldNotReceive('delete');

        app(Engine::class)->delete($default);

        Event::assertDispatched(DatabaseNotDeleted::class, function ($event) use ($default) {
            $this->assertEquals($default, $event->name);
            $this->assertEquals(DatabaseNotDeleted::DEFAULT, $event->reason);
            return true;
        });
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function current_builds_and_returns_a_database_object_using_the_connection_based_on_the_filer_current_database($connection, $name, $database)
    {
        $database = value($database);

        $this->mock(Connection::class)
            ->shouldReceive('database')
            ->once()
            ->withArgs([$name])
            ->andReturn($database)
            ->shouldReceive('name')
            ->andReturn($connection);

        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->once()
            ->andReturn($name);

        $db = app(Engine::class)->current();

        expect($db)->toBeInstanceOf(Database::class);
        expect($db->name)->toEqual($name);
        expect($db->db_database)->toEqual($database);
        expect($db->connection)->toEqual($connection);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function current_returns_null_if_the_filer_returns_null($connection, $name, $database, $default)
    {
        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($name)
            ->shouldNotReceive('database');

        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->once()
            ->andReturn(null);

        expect(app(Engine::class)->current())->toBeNull();
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
            ->shouldReceive('boot')
            ->once();

        app(Engine::class)->boot();
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

   /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function when_calling_delete_if_the_connection_is_not_resolvable_a_NoConnectionException_is_thrown($connection, $name, $database)
    {
        $this->app->bind(Connection::class, fn() => null);

        $this->expectException(NoConnectionException::class);

        app(Engine::class)->delete($name);
    }

   /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function when_calling_current_if_the_connection_is_not_resolvable_a_NoConnectionException_is_thrown($connection, $name, $database)
    {
        $this->app->bind(Connection::class, fn() => null);

        $this->expectException(NoConnectionException::class);

        app(Engine::class)->current();
    }

    public function databaseConnectionDataProvider()
    {
        return [
            ['sqlite', 'foobar', fn() => database_path('foobar.sqlite'), 'database'],
            ['mysql', 'foobar', 'hopper_foobar', 'hopper'],
        ];
    }
}
