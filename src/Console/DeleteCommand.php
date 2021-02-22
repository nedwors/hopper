<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Traits\ListensForEvents;

class DeleteCommand extends Command
{
    use ListensForEvents;

    protected $signature = 'hop:delete {database?}';

    protected $description = 'Delete the given hopper database';

    public function handle()
    {
        if (!$database = $this->argument('database')) {
            return $this->warn('Please provide a database to be deleted');
        }

        $this->listen();
        Hop::delete($database);
    }
}
