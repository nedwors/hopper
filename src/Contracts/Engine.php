<?php

namespace Nedwors\Hopper\Contracts;

use Nedwors\Hopper\Database;

interface Engine
{
    const DEFAULT = 'hopper-engine-default';

    /**
     * Configure the database to be used by the application
     */
    public function use(string $database);

    /**
     * Delete the given database
     */
    public function delete(string $database);

    /**
     * Returns a Database with the relevant connection information, or null if there is no current Hop
     * */
    public function current(): ?Database;

    /**
     * Boot the engine
     * */
    public function boot();
}
