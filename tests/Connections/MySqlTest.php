<?php

namespace Nedwors\Hopper\Tests\Connections;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Nedwors\Hopper\Connections\MySql;
use Nedwors\Hopper\Tests\TestCase;

class MySqlTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->config->set('database.default', 'mysql');
    }

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
            ->withArgs(function ($statement, $parameter) {
                expect($statement)->toEqual("CREATE DATABASE IF NOT EXISTS ?");
                expect($parameter)->toEqual(['hopper_hopper_test']);
                return true;
            });

        app(MySql::class)->create('hopper_test');
    }

    /** @test */
    public function any_dashes_will_be_replaced_with_underscores_on_create()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(function ($statement, $parameter) {
                expect($statement)->toEqual("CREATE DATABASE IF NOT EXISTS ?");
                expect($parameter)->toEqual(['hopper_test_database_with_underscores']);
                return true;
            });

        app(MySql::class)->create('test-database_with-underscores');
    }

    /** @test */
    public function the_delete_method_will_execute_a_db_statement()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(function ($statement, $parameter) {
                expect($statement)->toEqual("DROP DATABASE IF EXISTS ?");
                expect($parameter)->toEqual(['hopper_hopper_test']);
                return true;
            });

        app(MySql::class)->delete('hopper_test');
    }

    /** @test */
    public function any_dashes_will_be_replaced_with_underscores_on_delete()
    {
        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(function ($statement, $parameter) {
                expect($statement)->toEqual("DROP DATABASE IF EXISTS ?");
                expect($parameter)->toEqual(['hopper_test_database_with_underscores']);
                return true;
            });

        app(MySql::class)->delete('test-database_with-underscores');
    }

    /** @test */
    public function exists_will_execute_a_db_statement()
    {
        DB::partialMock()
            ->shouldReceive('select')
            ->once()
            ->withArgs(function ($statement, $parameter) {
                expect($statement)->toEqual("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
                expect($parameter)->toEqual(['hopper_hopper_test']);
                return true;
            })
            ->andReturn([]);

        app(MySql::class)->exists('hopper_test');
    }

    /** @test */
    public function any_dashes_will_be_replaced_with_underscores_on_exists()
    {
        DB::partialMock()
            ->shouldReceive('select')
            ->once()
            ->withArgs(function ($statement, $parameter) {
                expect($statement)->toEqual("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
                expect($parameter)->toEqual(['hopper_test_database_with_underscores']);
                return true;
            })
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
            ->withArgs(function ($statement, $parameter) {
                expect($statement)->toEqual("CREATE DATABASE IF NOT EXISTS ?");
                expect($parameter)->toEqual(['this_is_a_test_hopper_test']);
                return true;
            });

        app(MySql::class)->create('hopper_test');
    }

    /** @test */
    public function the_database_prefix_defaults_if_none_is_configured()
    {
        Config::set('hopper.connections.mysql.database-prefix', null);

        DB::partialMock()
            ->shouldReceive('statement')
            ->once()
            ->withArgs(function ($statement, $parameter) {
                expect($statement)->toEqual("CREATE DATABASE IF NOT EXISTS ?");
                expect($parameter)->toEqual(['hopper_hopper_test']);
                return true;
            });

        app(MySql::class)->create('hopper_test');
    }

    /** @test */
    public function database_will_return_the_database_name_for_the_given_name()
    {
        $database = app(MySql::class)->database('hopper_test');

        expect($database)->toEqual('hopper_hopper_test');
    }
}
