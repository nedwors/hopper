<?php

namespace Nedwors\Hopper\Connections;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Connection;
use Illuminate\Support\Str;

class Sqlite implements Connection
{
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

    public function database(string $name, bool $isDefault = false): string
    {
        if (!$isDefault) {
            $name = $this->hopperDirectory() . $name;
        }

        return database_path(Str::finish($name, '.sqlite'));
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
