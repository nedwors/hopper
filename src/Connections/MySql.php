<?php

namespace Nedwors\Hopper\Connections;

use Illuminate\Support\Facades\DB;
use Nedwors\Hopper\Contracts\Connection;

class MySql implements Connection
{
    const DEFAULT_PREFIX = 'hopper_';
    protected $prefix;

    public function __construct()
    {
        $this->prefix = config('hopper.connections.mysql.database-prefix') ?? self::DEFAULT_PREFIX;
    }

    public function create(string $name)
    {
        DB::statement("CREATE DATABASE IF NOT EXISTS ?", [$this->database($name)]);
    }

    public function exists(string $name): bool
    {
        return ! empty(DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$this->database($name)]));
    }

    public function delete(string $name): bool
    {
        DB::statement("DROP DATABASE IF EXISTS ?", [$this->database($name)]);

        return true;
    }

    public function database(string $name): string
    {
        $name = str_replace('-', '_', $name);

        return "{$this->prefix}$name";
    }

    public function name(): string
    {
        return 'mysql';
    }

    public function boot() {}
}
