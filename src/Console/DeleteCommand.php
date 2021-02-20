<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Nedwors\Hopper\Events\DatabaseDeleted;
use Nedwors\Hopper\Events\DatabaseNotDeleted;
use Nedwors\Hopper\Events\HoppedToDefault;
use Nedwors\Hopper\Facades\Hop;

class DeleteCommand extends Command
{
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

    protected function listen()
    {
        Event::listen(DatabaseDeleted::class, function ($event) {
            $this->info("Successfully deleted <fg=yellow>$event->name</>");
        });

        Event::listen(DatabaseNotDeleted::class, function ($event) {
            $this->info("<fg=yellow>$event->name</> {$this->notDeletedMessage($event->reason)}, so it was not deleted");
        });

        Event::listen(HoppedToDefault::class, function ($event) {
            $this->info("Hopped to default database: <fg=yellow>$event->name</>");
        });
    }

    protected function notDeletedMessage($reason)
    {
        return [
            DatabaseNotDeleted::DOES_NOT_EXIST => 'does not exist',
            DatabaseNotDeleted::DEFAULT => 'is the default database',
        ][$reason];
    }
}
