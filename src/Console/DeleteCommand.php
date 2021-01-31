<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Nedwors\Hopper\Facades\Hop;

class DeleteCommand extends Command
{
    protected $signature = 'hop:delete {database?}';

    protected $description = 'Delete the given hopper database';

    public function handle()
    {
        $database = $this->argument('database');

        if (!$database) {
            return $this->warn('Please provide a database to be deleted');
        }

        Hop::delete($database)
            ? $this->info("Successfully deleted <fg=yellow>$database</>")
            : $this->error("$database was not deleted");
    }
}
