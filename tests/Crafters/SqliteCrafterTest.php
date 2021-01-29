<?php

namespace Nedwors\Hopper\Tests\Crafters;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Crafter;
use Nedwors\Hopper\Crafters\SqliteCrafter;
use Nedwors\Hopper\Tests\TestCase;

class SqliteCrafterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->swap(Crafter::class, app(SqliteCrafter::class));
    }

    /** @test */
    public function create_will_create_a_new_sqlite_database_at_the_database_path()
    {
        File::partialMock()
            ->shouldReceive('put')
            ->once()
            ->withArgs(function ($database, $contents) {
                expect($database)->toEqual(database_path('foobar.sqlite'));
                expect($contents)->toEqual('');
                return true;
            });

        app(Crafter::class)->create('foobar.sqlite');
    }

    /** @test */
    public function create_will_add_the_sqlite_extension_if_needed()
    {
        File::partialMock()
            ->shouldReceive('put')
            ->once()
            ->withArgs(function ($database, $contents) {
                expect($database)->toEqual(database_path('foobar.sqlite'));
                expect($contents)->toEqual('');
                return true;
            });

        app(Crafter::class)->create('foobar');
    }

    /** @test */
    public function exists_returns_if_the_file_exists()
    {
        File::partialMock()
            ->shouldReceive('exists')
            ->once()
            ->withArgs(function ($database) {
                expect($database)->toEqual(database_path('foobar.sqlite'));
                return true;
            })
            ->andReturn($exists = rand(1, 2) == 1);

        expect(app(Crafter::class)->exists('foobar'))->toEqual($exists);
    }

    /** @test */
    public function a_database_will_not_be_created_if_it_already_exists_to_prevent_overwrites()
    {
        File::partialMock()
            ->shouldReceive('exists')
            ->once()
            ->andReturn(true)
            ->shouldNotReceive('put');

        app(Crafter::class)->create('foobar');
    }

    /** @test */
    public function delete_will_remove_the_given_database()
    {
        File::partialMock()
            ->shouldReceive('delete')
            ->once()
            ->withArgs(function ($database) {
                expect($database)->toEqual(database_path('foobar.sqlite'));
                return true;
            })
            ->andReturn(true);

        app(Crafter::class)->delete('foobar');
    }

    /** @test */
    public function delete_will_return_Files_boolean()
    {
        File::partialMock()
            ->shouldReceive('delete')
            ->once()
            ->withArgs(function ($database) {
                expect($database)->toEqual(database_path('foobar.sqlite'));
                return true;
            })
            ->andReturn($deleted = rand(1, 2) == 1);

        expect(app(Crafter::class)->delete('foobar'))->toEqual($deleted);
    }

}
