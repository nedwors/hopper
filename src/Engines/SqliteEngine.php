<?php

namespace Nedwors\Hopper\Engines;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Engine;
use Illuminate\Support\Str;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Database;

class SqliteEngine implements Engine
{
    protected Filer $filer;
    protected $databasePath;

    public function __construct(Filer $filer)
    {
        $this->filer = $filer;
        $this->databasePath = Str::finish(config('hopper.path'), '/');
    }

    public function use(string $database)
    {
        $this->createIfNeeded($database);

        $this->filer->setCurrentHop($database);
    }

    protected function createIfNeeded($database)
    {
        if ($this->isDefault($database)) {
            return;
        }

        $fileName = $this->normalize($database);

        if ($this->exists($fileName)) {
            return;
        }

        File::put($fileName, '');
    }

    protected function isDefault($database)
    {
        return $database == config('hopper.default-database');
    }

    public function current(): ?Database
    {
        $database = $this->filer->currentHop();

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
        if ($this->isDefault($database)) {
            return false;
        }

        return File::delete($this->normalize($database));
    }

    protected function normalize(string $database): string
    {
        if (!$this->isDefault($database)) {
            $database = $this->databasePath . $database;
        }

        return database_path(Str::finish($database, '.sqlite'));
    }

    public function connection(): string
    {
        return 'sqlite';
    }
}
