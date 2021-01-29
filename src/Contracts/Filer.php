<?php

namespace Nedwors\Hopper\Contracts;

interface Filer
{
    public function setCurrentHop(string $database);

    public function currentHop(): ?string;
}
