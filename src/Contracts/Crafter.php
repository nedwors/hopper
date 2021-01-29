<?php

namespace Nedwors\Hopper\Contracts;

interface Crafter
{
    public function create(string $database);

    public function exists(string $database): bool;

    public function delete(string $database): bool;
}
