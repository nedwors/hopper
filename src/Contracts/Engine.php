<?php

namespace Nedwors\Hopper\Contracts;

interface Engine
{
    public function use(string $database);

    public function exists(string $database): bool;

    public function delete(string $database): bool;
}
