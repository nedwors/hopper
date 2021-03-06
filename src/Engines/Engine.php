<?php

namespace Nedwors\Hopper\Engines;

use Nedwors\Hopper\Database;
use Nedwors\Hopper\Contracts;
use Nedwors\Hopper\Facades\Git;
use Illuminate\Support\Facades\DB;
use Nedwors\Hopper\Contracts\Filer;
use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Events\DatabaseDeleted;
use Nedwors\Hopper\Events\HoppedToDefault;
use Nedwors\Hopper\Events\HoppedToDatabase;
use Nedwors\Hopper\Events\DatabaseNotDeleted;
use Nedwors\Hopper\Exceptions\NoConnectionException;

class Engine implements Contracts\Engine
{
    protected Connection $connection;
    protected Filer $filer;
    protected $defaultDatabase = null;

    public function __construct(Filer $filer)
    {
        $this->connection = $this->resolveConnection();
        $this->filer = $filer;
        $this->defaultDatabase = config("database.connections.{$this->connection->name()}.database");
    }

    protected function resolveConnection()
    {
        return throw_unless(
            rescue(fn() => app(Connection::class), null, false),
            NoConnectionException::class
        );
    }

    public function use(string $database)
    {
        $this->isDefault($database)
            ? $this->useDefault()
            : $this->useNonDefault($database);
    }

    protected function useDefault()
    {
        $this->filer->flushCurrentHop();
        HoppedToDefault::dispatch($this->defaultDatabase);
    }

    protected function useNonDefault(string $database)
    {
        $database = $this->sanitize($database);

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

    protected function exists(string $database)
    {
        return $this->connection->exists($database);
    }

    public function delete(string $database)
    {
        if ($this->isDefault($database)) {
            DatabaseNotDeleted::dispatch($this->defaultDatabase, DatabaseNotDeleted::DEFAULT);
            return;
        }

        $database = $this->sanitize($database);

        if (!$this->exists($database)) {
            DatabaseNotDeleted::dispatch($database, DatabaseNotDeleted::DOES_NOT_EXIST);
            return;
        }

        $this->connection->delete($database);
        DatabaseDeleted::dispatch($database);
        $this->use($this->defaultDatabase);
    }

    public function current(): ?Database
    {
        if (!$name = $this->filer->currentHop()) {
            return null;
        }

        if (!$this->exists($name)) {
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
        return in_array($name, [
            $this->defaultDatabase,
            self::DEFAULT,
            Git::default()
        ]);
    }

    protected function sanitize($database)
    {
        return $this->connection->sanitize($database);
    }

    public function boot()
    {
        $this->connection->boot();

        if (!$database = $this->current()) {
            return;
        }

        DB::purge();

        Config::set(
            "database.connections.{$database->connection}.database",
            $database->db_database
        );
    }
}
