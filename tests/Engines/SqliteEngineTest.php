<?php

namespace Nedwors\Hopper\Tests\Engines;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Engines\SqliteEngine;
use Nedwors\Hopper\Tests\TestCase;

class SqliteEngineTest extends TestCase
{
    protected $databasePath = 'hopper';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('hopper.path', $this->databasePath);
        $this->swap(Engine::class, app(SqliteEngine::class));
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

        app(Engine::class)->use('foobar');
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

        expect(app(Engine::class)->exists('foobar'))->toEqual($exists);
    }

    /** @test */
    public function a_database_will_not_be_created_if_it_already_exists_to_prevent_overwrites()
    {
        File::partialMock()
            ->shouldReceive('exists')
            ->once()
            ->andReturn(true)
            ->shouldNotReceive('put');

        app(Engine::class)->use('foobar');
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

        app(Engine::class)->delete('foobar');
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

        expect(app(Engine::class)->delete('foobar'))->toEqual($deleted);
    }
}
