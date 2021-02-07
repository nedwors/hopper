<?php

namespace Nedwors\Hopper\Tests\Connections;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Nedwors\Hopper\Connections\MySql;
use Nedwors\Hopper\Tests\TestCase;

class MySqlTest extends TestCase
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

        app(MySql::class)->create('hopper_test');
    }

    /** @test */
    public function any_dashes_will_be_replaced_with_underscores_on_create()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['CREATE DATABASE IF NOT EXISTS hopper_test_database_with_underscores']);

        app(MySql::class)->create('test-database_with-underscores');
    }

    /** @test */
    public function the_delete_method_will_execute_a_db_statement()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['DROP DATABASE IF EXISTS hopper_hopper_test']);

        app(MySql::class)->delete('hopper_test');
    }

    /** @test */
    public function any_dashes_will_be_replaced_with_underscores_on_delete()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['DROP DATABASE IF EXISTS hopper_test_database_with_underscores']);

        app(MySql::class)->delete('test-database_with-underscores');
    }

    /** @test */
    public function exists_will_execute_a_db_statement()
    {
        DB::partialMock()
            ->shouldReceive('select')
            ->once()
            ->withArgs(["SHOW DATABASES LIKE 'hopper_hopper_test'"])
            ->andReturn([]);

        app(MySql::class)->exists('hopper_test');
    }

    /** @test */
    public function any_dashes_will_be_replaced_with_underscores_on_exists()
    {
        DB::partialMock()
            ->shouldReceive('select')
            ->once()
            ->withArgs(["SHOW DATABASES LIKE 'hopper_test_database_with_underscores'"])
            ->andReturn([]);

        app(MySql::class)->exists('test-database_with-underscores');
    }

    /** @test */
    public function the_database_prefix_is_configurable()
    {
        Config::set('hopper.connections.mysql.database-prefix', 'this_is_a_test_');

        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['CREATE DATABASE IF NOT EXISTS this_is_a_test_hopper_test']);

        app(MySql::class)->create('hopper_test');
    }

    /** @test */
    public function the_database_prefix_defaults_if_none_is_configured()
    {
        Config::set('hopper.connections.mysql.database-prefix', null);

        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(['CREATE DATABASE IF NOT EXISTS hopper_hopper_test']);

        app(MySql::class)->create('hopper_test');
    }

    /** @test */
    public function database_will_return_the_database_name_for_the_given_name()
    {
        $database = app(MySql::class)->database('hopper_test');

        expect($database)->toEqual('hopper_hopper_test');
    }

    /** @test */
    public function database_will_return_a_database_without_the_prefixed_db_connection_for_the_default_database()
    {
        $database = app(MySql::class)->database('hopper', $isDefault = true);

        expect($database)->toEqual('hopper');
    }
}
