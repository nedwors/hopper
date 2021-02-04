<?php

namespace Nedwors\Hopper\Connections;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Connection;
use Illuminate\Support\Str;

class Sqlite implements Connection
{
    public function create(string $name)
    {
        if ($this->exists($name)) {
            return;
        }

        File::put($this->database($name), '');
    }

    public function exists(string $name): bool
    {
        return File::exists($this->database($name));
    }

    public function delete(string $name): bool
    {
        if ($this->isDefault($name)) {
            return false;
        }

        return File::delete($this->database($name));
    }

    public function database(string $name): string
    {
        if (!$this->isDefault($name)) {
            $name = $this->hopperDirectory() . $name;
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
        if (!File::exists($hopperDirectory = database_path($this->hopperDirectory()))) {
            File::makeDirectory($hopperDirectory);
        }
    }

    protected function hopperDirectory()
    {
        return Str::finish(config('hopper.connections.sqlite.database-path', 'hopper/'), '/');
    }
}
