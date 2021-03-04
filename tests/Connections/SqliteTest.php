<?php

namespace Nedwors\Hopper\Tests\Connections;

use Nedwors\Hopper\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Connections\Sqlite;

class SqliteTest extends TestCase
{
    protected $databasePath = 'hopper';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('hopper.connections.sqlite.database-path', 'hopper/');
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
    public function database_returns_the_database_connection_required_for_the_database()
    {
        $database = app(Sqlite::class)->database('hello-world');

        expect($database)->toEqual(database_path("{$this->databasePath}/hello-world.sqlite"));
    }

    /** @test */
    public function when_it_boots_the_configured_hopper_directory_is_created_if_it_doesnt_exist()
    {
        Config::set('hopper.connections.sqlite.database-path', 'hopper/');

        File::partialMock()
            ->shouldReceive('ensureDirectoryExists')
            ->once()
            ->withArgs([database_path('hopper/')]);

        app(Sqlite::class)->boot();
    }

    /**
     * @dataProvider sanitizableNamesDataProvider
     * @test
     * */
    public function sanitize_will_sanitize_the_given_database_name_appropriate_for_sqlite_databases($unsanitized, $sanitized)
    {
        expect(app(Sqlite::class)->sanitize($unsanitized))->toEqual($sanitized);
    }

    public function sanitizableNamesDataProvider()
    {
        return [
            ['this-has-dashes', 'this-has-dashes'],
            ['this/has/slashes', 'this-has-slashes'],
        ];
    }
}
