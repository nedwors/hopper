<?php

namespace Nedwors\Hopper\Contracts;

use Nedwors\Hopper\Database;

interface Connection
{
    public function create(string $name);

    public function exists(string $name): bool;

    public function delete(string $name): bool;

    public function database(string $name): Database;

    public function boot();
}
