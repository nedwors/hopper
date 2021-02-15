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
        if (!$this->canBoot()) {
            return;
        }

        $this->engine->boot();
    }

    protected function canBoot()
    {
        return collect(config('hopper.boot-checks'))
                ->map(fn($check) => app($check))
                ->every(fn($check) => $check->check());
    }
}
