<?php

namespace Nedwors\Hopper\Contracts;

use Nedwors\Hopper\Database;

interface Engine
{
    const DEFAULT = 'hopper-engine-default';

    public function use(string $database);

    public function delete(string $database);

    public function current(): ?Database;

    public function boot();
}
