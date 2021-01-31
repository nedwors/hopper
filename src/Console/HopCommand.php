<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Nedwors\Hopper\Facades\Git;
use Nedwors\Hopper\Facades\Hop;

class HopCommand extends Command
{
    protected $signature = 'hop {database?}';

    protected $description = 'Hop to the given database';

    public function handle()
    {
        $database = $this->argument('database') ?? Git::current();

        if (!$database) {
            return $this->warn('Please hop on a git branch or provide a database name');
        }

        Hop::to($database);
        $this->info("Hopped to $database");
    }
}
