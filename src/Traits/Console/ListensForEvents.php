<?php

namespace Nedwors\Hopper\Traits\Console;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Events\DatabaseDeleted;
use Nedwors\Hopper\Events\HoppedToDefault;
use Nedwors\Hopper\Events\HoppedToDatabase;
use Nedwors\Hopper\Events\DatabaseNotDeleted;

trait ListensForEvents
{
    private static $events = [
        HoppedToDatabase::class,
        HoppedToDefault::class,
        DatabaseCreated::class,
        DatabaseDeleted::class,
        DatabaseNotDeleted::class
    ];

    protected function listen()
    {
        collect(static::$events)->each(function ($hopperEvent) {
            $eventMethod = Str::of($hopperEvent)->afterLast("\\")->camel()->__toString();
            $eventMessageMethod = $eventMethod . "Message";

            Event::listen(
                $hopperEvent,
                fn($event) => $this->{$eventMethod}($event, fn() => $this->{$eventMessageMethod}($event))
            );
        });
    }

    protected function hoppedToDatabase(HoppedToDatabase $event, $message)
    {
        $message();
    }

    protected function hoppedToDatabaseMessage(HoppedToDatabase $event)
    {
        return $this->info("Hopped to <fg=yellow>$event->name</>");
    }

    protected function hoppedToDefault(HoppedToDefault $event, $message)
    {
        $message();
    }

    protected function hoppedToDefaultMessage(HoppedToDefault $event)
    {
        return $this->info("Hopped to default database: <fg=yellow>$event->name</>");
    }

    protected function databaseCreated(DatabaseCreated $event, $message)
    {
        $message();
    }

    protected function databaseCreatedMessage(DatabaseCreated $event)
    {
        return $this->info("<fg=yellow>$event->name</> was created");
    }

    protected function databaseDeleted(DatabaseDeleted $event, $message)
    {
        $message();
    }

    protected function databaseDeletedMessage(DatabaseDeleted $event)
    {
        return $this->info("Successfully deleted <fg=yellow>$event->name</>");
    }

    protected function databaseNotDeleted(DatabaseNotDeleted $event, $message)
    {
        $message();
    }

    protected function databaseNotDeletedMessage(DatabaseNotDeleted $event)
    {
        return $this->info("<fg=yellow>$event->name</> {$this->notDeletedMessage($event->reason)}, so it was not deleted");
    }

    protected function notDeletedMessage($reason)
    {
        return [
            DatabaseNotDeleted::DOES_NOT_EXIST => 'does not exist',
            DatabaseNotDeleted::DEFAULT => 'is the default database',
            ][$reason];
    }
}
