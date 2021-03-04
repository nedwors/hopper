<?php

namespace Nedwors\Hopper\Contracts;

interface Filer
{
    /**
     * Stores the current database name
     */
    public function setCurrentHop(string $database);

    /**
     * Nullfies the current database name
     * */
    public function flushCurrentHop();

    /**
     * Returns the current database name, or null if none is set
     * */
    public function currentHop(): ?string;
}
