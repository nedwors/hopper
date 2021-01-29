<?php

namespace Nedwors\Hopper\Engines;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Engine;
use Illuminate\Support\Str;

class SqliteEngine implements Engine
{
    public function use(string $database)
    {
        $database = $this->normalize($database);

        if ($this->exists($database)) {
            return;
        }

        File::put($database, '');
    }

    public function exists(string $database): bool
    {
        return File::exists($this->normalize($database));
    }

    public function delete(string $database): bool
    {
        return File::delete($this->normalize($database));
    }

    protected function normalize($database)
    {
        return database_path(Str::finish($database, '.sqlite'));
    }
}
