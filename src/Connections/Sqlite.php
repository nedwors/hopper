<?php

namespace Nedwors\Hopper\Connections;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Connection;
use Illuminate\Support\Str;
use Nedwors\Hopper\Database;

class Sqlite implements Connection
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

    public function database(string $name): Database
    {
        return new Database($name, $this->toFilePath($name), 'sqlite');
    }

    protected function toFilePath(string $name): string
    {
        if (!$this->isDefault($name)) {
            $name = $this->applyDatabasePath($name);
        }

        return database_path(Str::finish($name, '.sqlite'));
    }

    protected function applyDatabasePath($database)
    {
        return Str::finish(config('hopper.drivers.sqlite.database-path'), '/') . $database;
    }

    protected function isDefault($name)
    {
        return $name === config('hopper.default-database');
    }

    public function boot()
    {
        # code...
    }
}