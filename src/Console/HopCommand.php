<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Nedwors\Hopper\Facades\Git;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Traits\ListensForEvents;

class HopCommand extends Command
{
    use ListensForEvents;

    protected $signature = 'hop {database?}';

    protected $description = 'Hop to the given database';

    protected $postCreationCallback = null;

    public function handle()
    {
        if (!$database = $this->argument('database') ?? Git::current()) {
            return $this->warn('Please hop on a git branch or provide a database name');
        }

        $this->listen();
        Hop::to($database);

        if (!$this->postCreationCallback) {
            return;
        }

        $this->postCreationCallback->__invoke();
    }

    protected function databaseCreated(DatabaseCreated $event, $message)
    {
        $message();
        $this->postCreationCallback = $this->postCreationCallback($event->name);
    }

    protected function postCreationCallback($name)
    {
        return function () use ($name) {
            if (!$this->confirm("Do you want to run the post-creation steps for <fg=yellow>$name</>?")) {
                return;
            }

            Hop::boot();
            $this->info('Ok, running now...');
            Hop::handlePostCreation();
            $this->info('All post-creation steps run');
        };
    }
}
