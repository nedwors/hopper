<?php

namespace Nedwors\Hopper\Tests\Connections;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Connections\SQLite;
use Nedwors\Hopper\Database;
use Nedwors\Hopper\Tests\TestCase;

class SQLiteTest extends TestCase
{
    protected $databasePath = 'hopper';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('hopper.connections.sqlite.database-path', $this->databasePath);
    }

    /** @test */
    public function create_will_create_a_new_sqlite_database_at_the_database_path_in_the_configured_hopper_directory()
    {
        File::partialMock()
            ->shouldReceive('put')
            ->once()
            ->withArgs(function ($database, $contents) {
                expect($database)->toEqual(database_path("{$this->databasePath}/foobar.sqlite"));
                expect($contents)->toEqual('');
                return true;
            });

        app(SQLite::class)->create('foobar');
    }

    /** @test */
    public function exists_returns_if_the_database_exists()
    {
        File::partialMock()
            ->shouldReceive('exists')
            ->once()
            ->withArgs(function ($database) {
                expect($database)->toEqual(database_path("{$this->databasePath}/foobar.sqlite"));
                return true;
            })
            ->andReturn($exists = rand(1, 2) == 1);

        expect(app(SQLite::class)->exists('foobar'))->toEqual($exists);
    }

    /** @test */
    public function a_database_will_not_be_created_if_it_already_exists_to_prevent_overwrites()
    {
        File::partialMock()
            ->shouldReceive('exists')
            ->once()
            ->andReturn(true)
            ->shouldNotReceive('put')
            ->withArgs(fn($database) => $database == database_path("{$this->databasePath}/foobar.sqlite"));

        app(SQLite::class)->create('foobar');
    }

    /** @test */
    public function delete_will_remove_the_given_database()
    {
        File::partialMock()
            ->shouldReceive('delete')
            ->once()
            ->withArgs(function ($database) {
                expect($database)->toEqual(database_path("{$this->databasePath}/foobar.sqlite"));
                return true;
            })
            ->andReturn(true);

        app(SQLite::class)->delete('foobar');
    }

    /** @test */
    public function delete_will_return_Files_boolean()
    {
        File::partialMock()
            ->shouldReceive('delete')
            ->once()
            ->withArgs(function ($database) {
                expect($database)->toEqual(database_path("{$this->databasePath}/foobar.sqlite"));
                return true;
            })
            ->andReturn($deleted = rand(1, 2) == 1);

        expect(app(SQLite::class)->delete('foobar'))->toEqual($deleted);
    }

    /** @test */
    public function calling_delete_with_the_configured_default_database_name_will_not_delete_the_database()
    {
        Config::set('hopper.default-database', 'database');

        File::partialMock()->shouldNotReceive('delete');

        app(SQLite::class)->delete('database');
    }

    /** @test */
    public function database_returns_a_database_object_based_on_the_given_name()
    {
        $database = app(SQLite::class)->database('hello-world');

        expect($database)->toBeInstanceOf(Database::class);
        expect($database->name)->toEqual('hello-world');
        expect($database->db_database)->toEqual(database_path("{$this->databasePath}/hello-world.sqlite"));
        expect($database->connection)->toEqual('sqlite');
    }

    /** @test */
    public function the_default_database_is_returned_without_the_directory_as_it_is_in_the_root_of_the_database_path()
    {
        Config::set('hopper.default-database', 'database');

        $database = app(SQLite::class)->database('database');

        expect($database)->toBeInstanceOf(Database::class);
        expect($database->name)->toEqual('database');
        expect($database->db_database)->toEqual(database_path("database.sqlite"));
    }

    /** @test */
    public function when_it_boots_the_configured_hopper_directory_is_created_if_it_doesnt_exist()
    {
        Config::set('hopper.connections.sqlite.datbase-path', 'hopper/');

        File::partialMock()
            ->shouldReceive('exists')
            ->once()
            ->withArgs([database_path('hopper/')])
            ->andReturn(false)
            ->shouldReceive('makeDirectory')
            ->once()
            ->withArgs([database_path('hopper/')]);

        app(SQLite::class)->boot();
    }
}
