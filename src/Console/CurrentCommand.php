<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Traits\Console\CatchesExceptions;

class CurrentCommand extends Command
{
    use CatchesExceptions;

    protected $signature = 'hop:current';

    protected $description = 'Show the current hopper database connection';

    public function handle()
    {
        $this->tryTo(function () {
            $current = Hop::current();
            $this->info($current ? "Currently using <fg=yellow>$current->name</>" :  'Currently using the default database');
        });
    }
}
