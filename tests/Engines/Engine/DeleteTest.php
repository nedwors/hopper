<?php
namespace Nedwors\Hopper\Tests\Engines\Engine;

use Nedwors\Hopper\Engines\Engine;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Filer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Events\DatabaseDeleted;
use Nedwors\Hopper\Events\DatabaseNotDeleted;
use Nedwors\Hopper\Exceptions\NoConnectionException;

class DeleteTest extends TestCase
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
    public function delete_will_ask_the_connection_to_delete_the_given_database_if_it_exists($connection, $name, $database, $default)
    {
        $this->mock(Connection::class)
            ->shouldReceive('sanitize')
            ->andReturn($name)
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
            ->shouldReceive('sanitize')
            ->andReturn($name)
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
            ->shouldReceive('sanitize')
            ->andReturn($name)
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
            ->shouldReceive('sanitize')
            ->andReturn($name)
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
            ->shouldReceive('sanitize')
            ->andReturn($name)
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
            ->shouldReceive('sanitize')
            ->andReturn($name)
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
            ->shouldReceive('sanitize')
            ->andReturn($name)
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
    public function when_calling_delete_if_the_connection_is_not_resolvable_a_NoConnectionException_is_thrown($connection, $name, $database)
    {
        $this->app->bind(Connection::class, fn() => null);

        $this->expectException(NoConnectionException::class);

        app(Engine::class)->delete($name);
    }
}
