<?php
namespace Nedwors\Hopper\Tests\Engines\Engine;

use Nedwors\Hopper\Database;
use Nedwors\Hopper\Engines\Engine;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Filer;
use Illuminate\Support\Facades\Event;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Exceptions\NoConnectionException;

class CurrentTest extends TestCase
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
    public function current_builds_and_returns_a_database_object_using_the_connection_based_on_the_filer_current_database($connection, $name, $database)
    {
        $database = value($database);

        $this->mock(Connection::class)
            ->shouldReceive('database')
            ->once()
            ->withArgs([$name])
            ->andReturn($database)
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->andReturn(true);

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
    public function current_returns_null_if_the_connection_returns_false_for_exists($connection, $name, $database, $default)
    {
        $database = value($database);

        $this->mock(Connection::class)
            ->shouldNotReceive('database')
            ->shouldReceive('name')
            ->andReturn($connection)
            ->shouldReceive('exists')
            ->andReturn(false);

        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->once()
            ->andReturn($name);

        expect(app(Engine::class)->current())->toBeNull();
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
}
