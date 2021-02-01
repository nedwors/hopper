<?php

namespace Nedwors\Hopper\Tests\Connections;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Nedwors\Hopper\Connections\MySQL;
use Nedwors\Hopper\Database;
use Nedwors\Hopper\Tests\TestCase;

class MySQLTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('hopper.connections.mysql.database-prefix', 'hopper_');
    }

    /** @test */
    public function the_create_method_will_execute_a_db_statement()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['CREATE DATABASE IF NOT EXISTS hopper_hopper_test']);

        app(MySQL::class)->create('hopper_test');
    }

    /** @test */
    public function any_dashes_will_be_replaced_with_underscores_on_create()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['CREATE DATABASE IF NOT EXISTS hopper_test_database_with_underscores']);

        app(MySQL::class)->create('test-database_with-underscores');
    }

    /** @test */
    public function the_delete_method_will_execute_a_db_statement()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['DROP DATABASE IF EXISTS hopper_hopper_test']);

        app(MySQL::class)->delete('hopper_test');
    }

    /** @test */
    public function any_dashes_will_be_replaced_with_underscores_on_delete()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['DROP DATABASE IF EXISTS hopper_test_database_with_underscores']);

        app(MySQL::class)->delete('test-database_with-underscores');
    }

    /** @test */
    public function exists_will_execute_a_db_statement()
    {
        DB::partialMock()
            ->shouldReceive('select')
            ->once()
            ->withArgs(["SHOW DATABASES LIKE 'hopper_hopper_test'"])
            ->andReturn([]);

        app(MySQL::class)->exists('hopper_test');
    }

    /** @test */
    public function any_dashes_will_be_replaced_with_underscores_on_exists()
    {
        DB::partialMock()
            ->shouldReceive('select')
            ->once()
            ->withArgs(["SHOW DATABASES LIKE 'hopper_test_database_with_underscores'"])
            ->andReturn([]);

        app(MySQL::class)->exists('test-database_with-underscores');
    }

    /** @test */
    public function database_will_return_a_database_with_the_prefixed_db_connection()
    {
        $database = app(MySQL::class)->database('hopper_test');

        expect($database)->toBeInstanceOf(Database::class);
        expect($database->name)->toEqual('hopper_test');
        expect($database->db_database)->toEqual('hopper_hopper_test');
    }

    /** @test */
    public function the_database_prefix_is_configurable()
    {
        Config::set('hopper.connections.mysql.database-prefix', 'this_is_a_test_');

        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['CREATE DATABASE IF NOT EXISTS this_is_a_test_hopper_test']);

        app(MySQL::class)->create('hopper_test');
    }

    /** @test */
    public function the_database_prefix_defaults_if_none_is_configured()
    {
        Config::set('hopper.connections.mysql.database-prefix', null);

        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['CREATE DATABASE IF NOT EXISTS hopper_hopper_test']);

        app(MySQL::class)->create('hopper_test');
    }
}
