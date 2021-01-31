<?php

namespace Nedwors\Hopper;

use Illuminate\Support\Facades\Config;
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
        if (!env('APP_KEY')) {
            return false;
        }

        if (Config::get('app.env') === "production") {
            return false;
        }

        return true;
    }
}
