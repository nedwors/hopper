<?php

namespace Nedwors\Hopper\Engines;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Database;

class Engine implements Contracts\Engine
{
    protected Connection $connection;
    protected Filer $filer;

    public function __construct(Connection $connection, Filer $filer)
    {
        $this->connection = $connection;
        $this->filer = $filer;
    }

    public function use(string $database)
    {
        $database = $this->isDefaultGitBranch($database)
                        ? $this->defaultDatabase()
                        : $database;

        if ($this->shouldCreate($database)) {
            $this->connection->create($database);
        }

        $this->filer->setCurrentHop($database);
    }

    protected function isDefaultGitBranch(string $name): string
    {
        if (!$gitBranch = config('hopper.default-branch')) {
            return false;
        }

        if ($name != $gitBranch) {
            return false;
        }

        return true;
    }

    protected function shouldCreate(string $database): bool
    {
        if ($this->isDefault($database)) {
            return false;
        }

        if ($this->connection->exists($database)) {
            return false;
        }

        return true;
    }

    public function exists(string $database): bool
    {
        return $this->connection->exists($database);
    }

    public function delete(string $database): bool
    {
        if ($this->isDefault($database)) {
            return false;
        }

        if (!$this->connection->exists($database)) {
            return false;
        }

        return $this->connection->delete($database);
    }

    public function current(): ?Database
    {
        $database = $this->filer->currentHop();

        if (!$database) {
            return null;
        }

        return $this->connection->database($database);
    }

    protected function isDefault(string $name)
    {
        return $name === $this->defaultDatabase();
    }

    protected function defaultDatabase()
    {
        return config('hopper.default-database');
    }

    public function boot()
    {
        $this->connection->boot();

        if (!$database = $this->current()) {
            return;
        }

        Config::set(
            "database.connections.{$database->connection}.database",
            env('DB_DATABASE', $database->db_database)
        );
    }
}
