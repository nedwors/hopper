<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;
use Nedwors\Hopper\Facades\Git;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Traits\Console\ListensForEvents;
use Nedwors\Hopper\Traits\Console\CatchesExceptions;

class HopCommand extends Command
{
    use ListensForEvents;
    use CatchesExceptions;

    protected $signature = 'hop {database?} {--d}';

    protected $description = 'Hop to the given database';

    protected $postCreationCallback = null;

    public function handle()
    {
        if (!$database = $this->retrieveDatabaseInput()) {
            return $this->warn('Please hop on a git branch or provide a database name');
        }

        $this->listen();
        $this->tryTo(fn() => Hop::to($database));

        if (!$this->postCreationCallback) {
            return;
        }

        $this->postCreationCallback->__invoke();
    }

    protected function retrieveDatabaseInput()
    {
        return $this->option('d')
            ? Hop::getFacadeRoot()::DEFAULT
            : $this->argument('database') ?? Git::current();
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
            Hop::handlePostCreation($this);
            $this->info('All post-creation steps run');
        };
    }
}
