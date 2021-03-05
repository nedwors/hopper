<?php

namespace Nedwors\Hopper\Contracts;

interface Connection
{
    /**
     * Sanitizes the given name of unsupported characters
     */
    public function sanitize(string $name): string;

    /**
     * Returns the connection name
     * */
    public function name(): string;

    /**
     * Creates a new database
     */
    public function create(string $name);

    /**
     * Determines if the given database exists
     */
    public function exists(string $name): bool;

    /**
     * Deletes the database
     */
    public function delete(string $name): bool;

    /**
     * For the given name, returns the Laravel DB_DATABASE value
     */
    public function database(string $name): string;

    /**
     * Runs any necessary actions prior to booting the Hopper engine
     * */
    public function boot();
}
