<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Nedwors\Hopper\Facades\Hop;

class CurrentCommand extends Command
{
    protected $signature = 'hop:current';

    protected $description = 'Show the current hopper database';

    public function handle()
    {
        $this->info(Hop::current() ?? 'There is no current hopper db...');
    }
}
