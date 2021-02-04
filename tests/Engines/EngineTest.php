<?php
namespace Nedwors\Hopper\Tests\Engines;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Database;
use Nedwors\Hopper\Engines\Engine;
use Nedwors\Hopper\Tests\TestCase;

class EngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(Filer::class)->shouldReceive('setCurrentHop');
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function use_will_ask_the_connection_to_create_a_new_database_if_it_does_not_exist($connection, $name, $database, $default)
    {
        $database = is_callable($database) ? $database() : $database;
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
    public function use_will_not_ask_the_connection_to_create_a_new_database_if_it_exists($connection, $name, $database, $default)
    {
        $database = is_callable($database) ? $database() : $database;
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
        $database = is_callable($database) ? $database() : $database;
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
        $database = is_callable($database) ? $database() : $database;
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
    public function calling_use_with_the_configured_default_database_name_will_not_create_a_database_but_still_file_the_current_hop($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->once()
            ->andReturn($connection)
            ->shouldNotReceive('create');

        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop')
            ->once()
            ->withArgs([$default]);

        app(Engine::class)->use($default);
    }

    /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function exists_returns_the_connections_exists_method_return_value($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->once()
            ->withArgs([$name])
            ->andReturn($exists = rand(1,2) == 1);

        expect(app(Engine::class)->exists($name))->toEqual($exists);
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
    public function delete_will_return_the_connections_deletion_boolean($connection, $name, $database, $default)
    {
        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->once()
            ->withArgs([$name])
            ->andReturn(true)
            ->shouldReceive('delete')
            ->withArgs([$name])
            ->andReturn($deleted = rand(1,2) == 1);

        expect(app(Engine::class)->delete('foobar'))->toEqual($deleted);
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
    public function current_builds_and_returns_a_database_object_using_the_connection_based_on_the_filer_current_database($connection, $name, $database)
    {
        $database = is_callable($database) ? $database() : $database;

        $this->mock(Connection::class)
            ->shouldReceive('database')
            ->once()
            ->withArgs([$name])
            ->andReturn($database)
            ->shouldReceive('name')
            ->once()
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
    public function if_the_database_to_be_used_is_the_configured_default_git_branch_the_configured_default_database_is_used($connection, $name, $database, $default)
    {
        Config::set("database.connections.$connection.database", $default);
        Config::set('hopper.default-branch', 'staging');

        $this->mock(Connection::class)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldNotReceive('create');

        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop')
            ->once()
            ->withArgs([$default]);

        app(Engine::class)->use('staging');
    }

   /**
     * @dataProvider databaseConnectionDataProvider
     * @test
     * */
    public function calling_boot_will_set_the_database_config_database_to_the_current_hop_for_the_configured_database_driver($connection, $name, $databaseFile)
    {
        $database = is_callable($databaseFile) ? $databaseFile() : $databaseFile;

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
            ->once()
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
        $database = is_callable($database) ? $database() : $database;

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

    public function databaseConnectionDataProvider()
    {
        return [
            ['sqlite', 'foobar', fn() => database_path('foobar.sqlite'), 'database'],
            ['mysql', 'foobar', 'hopper_foobar', 'hopper'],
        ];
    }
}
