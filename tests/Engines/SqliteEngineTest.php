<?php

namespace Nedwors\Hopper\Tests\Engines;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Database;
use Nedwors\Hopper\Engines\SqliteEngine;
use Nedwors\Hopper\Tests\TestCase;

class SqliteEngineTest extends TestCase
{
    protected $databasePath = 'hopper';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('hopper.path', $this->databasePath);
        $this->mock(Filer::class)->shouldReceive('setCurrentHop');
    }

    /** @test */
    public function use_will_create_a_new_sqlite_database_at_the_database_path_in_the_configured_hopper_directory()
    {
        File::partialMock()
            ->shouldReceive('put')
            ->once()
            ->withArgs(function ($database, $contents) {
                expect($database)->toEqual(database_path("{$this->databasePath}/foobar.sqlite"));
                expect($contents)->toEqual('');
                return true;
            });

        app(SqliteEngine::class)->use('foobar');
    }

    /** @test */
    public function use_will_file_the_currentHop_by_its_name()
    {
        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop')
            ->once()
            ->withArgs(['foobar']);

        File::partialMock()
            ->shouldReceive('put');

        app(SqliteEngine::class)->use('foobar');
    }

    /** @test */
    public function use_will_file_the_currentHop_even_if_the_database_is_not_created()
    {
        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop')
            ->once()
            ->withArgs(['foobar']);

        File::partialMock()
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldNotReceive('put');

        app(SqliteEngine::class)->use('foobar');
    }

    /** @test */
    public function exists_returns_if_the_file_exists()
    {
        File::partialMock()
            ->shouldReceive('exists')
            ->once()
            ->withArgs(function ($database) {
                expect($database)->toEqual(database_path("{$this->databasePath}/foobar.sqlite"));
                return true;
            })
            ->andReturn($exists = rand(1, 2) == 1);

        expect(app(SqliteEngine::class)->exists('foobar'))->toEqual($exists);
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

        app(SqliteEngine::class)->use('foobar');
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

        app(SqliteEngine::class)->delete('foobar');
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

        expect(app(SqliteEngine::class)->delete('foobar'))->toEqual($deleted);
    }

    /** @test */
    public function current_returns_a_database_object_based_on_the_filer_current_database()
    {
        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->once()
            ->andReturn('hello-world');

        $database = app(SqliteEngine::class)->current();

        expect($database)->toBeInstanceOf(Database::class);
        expect($database->name)->toEqual('hello-world');
        expect($database->db_database)->toEqual(database_path("{$this->databasePath}/hello-world.sqlite"));
    }

    /** @test */
    public function current_returns_null_if_the_filer_returns_null()
    {
        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->once()
            ->andReturn(null);

        $database = app(SqliteEngine::class)->current();

        expect($database)->toBeNull();
    }

    /** @test */
    public function calling_use_with_the_configured_default_database_name_will_not_create_a_database()
    {
        Config::set('hopper.default-database', 'database');

        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop');

        File::partialMock()
            ->shouldNotReceive('put');

        app(SqliteEngine::class)->use('database');
    }

    /** @test */
    public function calling_delete_with_the_configured_default_database_name_will_not_delete_the_database()
    {
        Config::set('hopper.default-database', 'database');

        File::partialMock()
            ->shouldNotReceive('delete');

        app(SqliteEngine::class)->delete('database');
    }

    /** @test */
    public function the_default_database_is_returned_with_current_without_the_directory_as_it_is_in_the_root_of_the_database_path()
    {
        Config::set('hopper.default-database', 'database');

        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->once()
            ->andReturn('database');

        $database = app(SqliteEngine::class)->current();

        expect($database)->toBeInstanceOf(Database::class);
        expect($database->name)->toEqual('database');
        expect($database->db_database)->toEqual(database_path("database.sqlite"));
    }
}
