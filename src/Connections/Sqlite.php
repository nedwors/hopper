<?php

namespace Nedwors\Hopper\Connections;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Connection;
use Illuminate\Support\Str;

class SQLite implements Connection
{
    public function create(string $name)
    {
        $fileName = $this->toFilePath($name);

        if ($this->exists($fileName)) {
            return;
        }

        File::put($fileName, '');
    }

    public function exists(string $name): bool
    {
        return File::exists($this->toFilePath($name));
    }

    public function delete(string $name): bool
    {
        if ($this->isDefault($name)) {
            return false;
        }

        return File::delete($this->toFilePath($name));
    }

    public function database(string $name): string
    {
        return $this->toFilePath($name);
    }

    protected function toFilePath(string $name): string
    {
        if (!$this->isDefault($name)) {
            $name = $this->databasePath() . $name;
        }

        return database_path(Str::finish($name, '.sqlite'));
    }

    protected function isDefault($name)
    {
        return $name === config('database.connections.sqlite.database');
    }

    public function name(): string
    {
        return 'sqlite';
    }

    public function boot()
    {
        if (!File::exists($databasePath = database_path($this->databasePath()))) {
            File::makeDirectory($databasePath);
        }
    }

    protected function databasePath()
    {
        return Str::finish(config('hopper.connections.sqlite.database-path', 'hopper/'), '/');
    }
}
