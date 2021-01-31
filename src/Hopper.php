<?php

namespace Nedwors\Hopper;

use Nedwors\Hopper\Contracts\Engine;

class Hopper
{
    protected Engine $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    public function to(string $database)
    {
        $this->engine->use($database);
    }

    public function current(): ?Database
    {
        return $this->engine->current();
    }

    public function delete(string $database): bool
    {
        return $this->engine->delete($database);
    }

    public function boot()
    {
        $this->engine->boot();
    }
}
