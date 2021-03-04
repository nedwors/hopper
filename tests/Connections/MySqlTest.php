<?php

namespace Nedwors\Hopper\Tests\Connections;

use Illuminate\Support\Facades\DB;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Connections\MySql;
use Illuminate\Support\Facades\Config;

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

    /**
     * @dataProvider sanitizableNamesDataProvider
     * @test
     * */
    public function sanitize_will_sanitize_the_given_database_name_appropriate_for_mysql_databases($unsanitized, $sanitized)
    {
        expect(app(MySql::class)->sanitize($unsanitized))->toEqual($sanitized);
    }

    public function sanitizableNamesDataProvider()
    {
        return [
            ['this-has-dashes', 'this_has_dashes'],
            ['has-some_dashes', 'has_some_dashes'],
            ['no_dashes', 'no_dashes'],
        ];
    }
}
