<?php

namespace Nedwors\Hopper\Contracts;

interface BootCheck
{
    /**
     * Determines if Hopper should configure the database connection
     * */
    public function check(): bool;
}
