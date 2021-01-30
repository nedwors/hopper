<?php

namespace Nedwors\Hopper;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Contracts\Filer;

class Hopper
{
    protected Engine $engine;
    protected Filer $filer;

    public function __construct(Engine $engine, Filer $filer)
    {
        $this->engine = $engine;
        $this->filer = $filer;
    }

    public function to(string $database)
    {
        rescue(function () use ($database) {
            $this->engine->use($database);
        }, null, false);
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

        if (!$current = $this->filer->currentHop()) {
            return;
        }

        Config::set(
            "database.connections.{$this->engine->connection()}.database",
            env('DB_DATABASE', $this->engine->normalize($current))
        );
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
