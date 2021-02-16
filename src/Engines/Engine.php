<?php

namespace Nedwors\Hopper\Engines;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Database;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Events\DatabaseDeleted;
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

    protected function useDefault()
    {
        $this->filer->flushCurrentHop();
    }

    protected function useNonDefault(string $database)
    {
        $this->createIfNeeded($database);

        $this->filer->setCurrentHop($database);
    }

    protected function createIfNeeded($database)
    {
        if ($this->exists($database)) {
            return;
        }

        $this->connection->create($database);
        DatabaseCreated::dispatch($database);
    }

    public function exists(string $database): bool
    {
        return $this->connection->exists($database);
    }

    public function delete(string $database)
    {
        $database = $this->resolveDatabaseName($database);

        if ($this->isDefault($database)) {
            return;
        }

        if (!$this->exists($database)) {
            return;
        }

        $this->connection->delete($database);
        DatabaseDeleted::dispatch($database);
    }

    protected function resolveDatabaseName(string $database)
    {
        return $database === Git::default() ? $this->defaultDatabase() : $database;
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
            $database->db_database
        );
    }
}
