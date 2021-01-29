<?php

namespace Nedwors\Hopper;

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
            $this->filer->setCurrentHop($database);
        });

    }
}
