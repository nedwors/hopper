<?php

namespace Nedwors\Hopper\Engines;

use Nedwors\Hopper\Database;
use Nedwors\Hopper\Contracts;
use Nedwors\Hopper\Facades\Git;
use Nedwors\Hopper\Contracts\Filer;
use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Events\DatabaseDeleted;
use Nedwors\Hopper\Events\HoppedToDefault;
use Nedwors\Hopper\Events\HoppedToDatabase;
use Nedwors\Hopper\Events\DatabaseNotDeleted;

class Engine implements Contracts\Engine
{
    protected Connection $connection;
    protected Filer $filer;
    protected $defaultDatabase = null;

    public function __construct(Connection $connection, Filer $filer)
    {
        $this->connection = $connection;
        $this->filer = $filer;
    }

    public function use(string $database)
    {
        $database = $this->resolveDatabaseName($database);

        $this->isDefault($database)
            ? $this->useDefault($database)
            : $this->useNonDefault($database);
    }

    protected function useDefault($database)
    {
        $this->filer->flushCurrentHop();
        HoppedToDefault::dispatch($this->defaultDatabase ?? $database);
    }

    protected function useNonDefault(string $database)
    {
        $this->createIfNeeded($database);

        $this->filer->setCurrentHop($database);
        HoppedToDatabase::dispatch($database);
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
            DatabaseNotDeleted::dispatch($database, DatabaseNotDeleted::DEFAULT);
            return;
        }

        if (!$this->exists($database)) {
            DatabaseNotDeleted::dispatch($database, DatabaseNotDeleted::DOES_NOT_EXIST);
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

        $this->defaultDatabase = $this->defaultDatabase();

        Config::set(
            "database.connections.{$database->connection}.database",
            $database->db_database
        );
    }
}
