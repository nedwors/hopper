<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Traits\Console\ListensForEvents;
use Nedwors\Hopper\Traits\Console\CatchesExceptions;

class DeleteCommand extends Command
{
    use ListensForEvents;
    use CatchesExceptions;

    protected $signature = 'hop:delete {database}';

    protected $description = 'Delete the given hopper database';

    public function handle()
    {
        $this->listen();
        $this->tryTo(fn() => Hop::delete($this->argument('database')));
    }
}
