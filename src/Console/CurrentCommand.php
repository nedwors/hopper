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

        $this->info($current ? $current->name :  'There is no current hopper db...');
    }
}
