<?php

namespace Nedwors\Hopper\Connections;

use Illuminate\Support\Facades\DB;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Database;

class MySQL implements Connection
{
    const PREFIX = 'hopper_';

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

    public function database(string $name): Database
    {
        return new Database($name, $this->sanitize($name), 'mysql');
    }

    protected function sanitize(string $name): string
    {
        return self::PREFIX . str_replace('-', '_', $name);
    }

    public function boot()
    {

    }
}
