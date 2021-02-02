<?php

namespace Nedwors\Hopper\Tests\Connections;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Connections\Sqlite;
use Nedwors\Hopper\Tests\TestCase;

class SqliteTest extends TestCase
{
    protected $databasePath = 'hopper';

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

        app(Sqlite::class)->create('foobar');
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

        expect(app(Sqlite::class)->exists('foobar'))->toEqual($exists);
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

        app(Sqlite::class)->create('foobar');
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

        app(Sqlite::class)->delete('foobar');
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

        expect(app(Sqlite::class)->delete('foobar'))->toEqual($deleted);
    }

    /** @test */
    public function calling_delete_with_the_configured_default_database_name_will_not_delete_the_database()
    {
        Config::set('database.connections.sqlite.database', 'database');

        File::partialMock()->shouldNotReceive('delete');

        app(Sqlite::class)->delete('database');
    }

    /** @test */
    public function database_returns_the_database_connection_required_for_the_database()
    {
        $database = app(Sqlite::class)->database('hello-world');

        expect($database)->toEqual(database_path("{$this->databasePath}/hello-world.sqlite"));
    }

    /** @test */
    public function the_default_database_is_returned_without_the_directory()
    {
        Config::set('database.connections.sqlite.database', 'database');

        $database = app(Sqlite::class)->database('database');

        expect($database)->toEqual(database_path("database.sqlite"));
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

        app(Sqlite::class)->boot();
    }
}
