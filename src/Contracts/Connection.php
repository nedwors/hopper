<?php

namespace Nedwors\Hopper\Contracts;

interface Connection
{
    public function name(): string;

    public function create(string $name);

    public function exists(string $name): bool;

    public function delete(string $name): bool;

    public function database(string $name): string;

    public function boot();
}
