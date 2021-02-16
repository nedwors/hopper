<?php

namespace Nedwors\Hopper\Engines;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Database;
use Nedwors\Hopper\Facades\Git;

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
        $database = $this->resolveDatabaseName($database);

        $this->isDefault($database)
            ? $this->useDefault()
            : $this->useNonDefault($database);
    }

    protected function resolveDatabaseName(string $database)
    {
        return $database === Git::default() ? $this->defaultDatabase() : $database;
    }

    protected function useDefault()
    {
        $this->filer->flushCurrentHop();
    }

    protected function useNonDefault(string $database)
    {
        if (!$this->exists($database)) {
            $this->connection->create($database);
        }

        $this->filer->setCurrentHop($database);
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

        if (!$this->exists($database)) {
            return false;
        }

        return $this->connection->delete($database);
    }

    public function current(): ?Database
    {
        if (!$name = $this->filer->currentHop()) {
            return null;
        }

        return new Database(
            $name,
            $this->connection->database($name),
            $this->connection->name()
        );
    }

    protected function isDefault(string $name)
    {
        return $name === $this->defaultDatabase();
    }

    protected function defaultDatabase()
    {
        return config("database.connections.{$this->connection->name()}.database");
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
