<?php

namespace Nedwors\Hopper\Contracts;

interface Filer
{
    public function setCurrentHop(string $database);

    public function flushCurrentHop();

    public function currentHop(): ?string;
}
