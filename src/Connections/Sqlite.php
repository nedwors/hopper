<?php

namespace Nedwors\Hopper\Connections;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Connection;

class Sqlite implements Connection
{
    public function sanitize(string $name): string
    {
        return str_replace('/', '-', $name);
    }

    public function create(string $name)
    {
        File::put($this->database($name), '');
    }

    public function exists(string $name): bool
    {
        return File::exists($this->database($name));
    }

    public function delete(string $name): bool
    {
        return File::delete($this->database($name));
    }

    public function database(string $name): string
    {
        return database_path(Str::finish("{$this->hopperDirectory()}$name", '.sqlite'));
    }

    public function name(): string
    {
        return 'sqlite';
    }

    public function boot()
    {
        File::ensureDirectoryExists(database_path($this->hopperDirectory()));
    }

    protected function hopperDirectory()
    {
        return Str::finish(config('hopper.connections.sqlite.database-path', 'hopper/'), '/');
    }
}
