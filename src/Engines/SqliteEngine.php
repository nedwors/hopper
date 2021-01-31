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

    public function __construct(Filer $filer)
    {
        $this->filer = $filer;
    }

    public function use(string $database)
    {
        $database = $this->isAlias($database)
                        ? config('hopper.default-database')
                        : $database;

        $this->createIfNeeded($database);

        $this->filer->setCurrentHop($database);
    }

    protected function isAlias($database)
    {
        if (!$gitBranch = config('hopper.default-branch')) {
            return false;
        }

        if ($database != $gitBranch) {
            return false;
        }

        return true;
    }

    protected function createIfNeeded($database)
    {
        if ($this->isDefault($database)) {
            return;
        }

        $fileName = $this->toFilePath($database);

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
            ? new Database($database, $this->toFilePath($database), 'sqlite')
            : null;
    }

    public function exists(string $database): bool
    {
        return File::exists($this->toFilePath($database));
    }

    public function delete(string $database): bool
    {
        if ($this->isDefault($database)) {
            return false;
        }

        return File::delete($this->toFilePath($database));
    }

    protected function toFilePath(string $database): string
    {
        if (!$this->isDefault($database)) {
            $database = $this->applyDatabasePath($database);
        }

        return database_path(Str::finish($database, '.sqlite'));
    }

    protected function applyDatabasePath($database)
    {
        return Str::finish(config('hopper.drivers.sqlite.database-path'), '/') . $database;
    }
}
