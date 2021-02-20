<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Nedwors\Hopper\Facades\Hop;

class CurrentCommand extends Command
{
    protected $signature = 'hop:current';

    protected $description = 'Show the current hopper database connection';

    public function handle()
    {
        $current = Hop::current();

        $this->info($current ? "Currently using <fg=yellow>$current->name</>" :  'Currently using the default database');
    }
}
