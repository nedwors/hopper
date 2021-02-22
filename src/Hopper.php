<?php

namespace Nedwors\Hopper;

use Nedwors\Hopper\Contracts\Engine;
use Illuminate\Support\Facades\Artisan;

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
        $this->boot();
    }

    public function current(): ?Database
    {
        return $this->engine->current();
    }

    public function delete(string $database)
    {
        $this->engine->delete($database);
    }

    public function handlePostCreation()
    {
        collect(config('hopper.post-creation-steps'))->each(fn($step) => $this->runStep($step));
    }

    protected function runStep($step)
    {
        if (is_callable($step)) {
            return $step();
        }

        if (is_string($step)) {
            return Artisan::call($step);
        }
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
