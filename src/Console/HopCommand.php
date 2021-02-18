<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Events\HoppedToDatabase;
use Nedwors\Hopper\Events\HoppedToDefault;
use Nedwors\Hopper\Facades\Git;
use Nedwors\Hopper\Facades\Hop;

class HopCommand extends Command
{
    protected $signature = 'hop {database?}';

    protected $description = 'Hop to the given database';

    public function handle()
    {
        if (!$database = $this->argument('database') ?? Git::current()) {
            return $this->warn('Please hop on a git branch or provide a database name');
        }

        $this->listen();
        Hop::to($database);
    }

    protected function listen()
    {
        Event::listen(DatabaseCreated::class, function ($event) {
            $this->info("<fg=yellow>$event->name</> was created");
        });

        Event::listen(HoppedToDatabase::class, function ($event) {
            $this->info("Hopped to <fg=yellow>$event->name</>");
        });

        Event::listen(HoppedToDefault::class, function ($event) {
            $this->info("Hopped to default database: <fg=yellow>$event->name</>");
        });
    }
}
