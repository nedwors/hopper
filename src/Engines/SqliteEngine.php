<?php

namespace Nedwors\Hopper\Engines;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Engine;
use Illuminate\Support\Str;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Database;

class SqliteEngine implements Engine
{
    protected $databasePath;

    public function __construct()
    {
        $this->databasePath = Str::finish(config('hopper.path'), '/');
    }

    public function use(string $database)
    {
        $fileName = $this->normalize($database);

        if (!$this->exists($fileName)) {
            File::put($fileName, '');
        }

        app(Filer::class)->setCurrentHop($database);
    }

    public function current(): ?Database
    {
        $database = app(Filer::class)->currentHop();

        return $database
            ? new Database($database, $this->normalize($database))
            : null;
    }

    public function exists(string $database): bool
    {
        return File::exists($this->normalize($database));
    }

    public function delete(string $database): bool
    {
        return File::delete($this->normalize($database));
    }

    public function normalize(string $database): string
    {
        return database_path($this->databasePath . Str::finish($database, '.sqlite'));
    }

    public function connection(): string
    {
        return 'sqlite';
    }
}
