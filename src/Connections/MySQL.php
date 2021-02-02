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
        $name = $this->sanitize($name);

        DB::statement("CREATE DATABASE IF NOT EXISTS $name");
    }

    public function exists(string $name): bool
    {
        $name = $this->sanitize($name);

        return count(DB::select("SHOW DATABASES LIKE '$name'")) > 0;
    }

    public function delete(string $name): bool
    {
        $name = $this->sanitize($name);

        DB::statement("DROP DATABASE IF EXISTS $name");

        return true;
    }

    public function database(string $name): string
    {
        return $this->sanitize($name);
    }

    protected function sanitize(string $name): string
    {
        $name = str_replace('-', '_', $name);

        return $this->isDefault($name) ? $name : "{$this->prefix}$name";
    }

    protected function isDefault($name)
    {
        return $name === config('database.connections.mysql.database');
    }

    public function name(): string
    {
        return 'mysql';
    }

    public function boot() {}
}
