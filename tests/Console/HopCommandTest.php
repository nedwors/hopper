<?php

namespace Nedwors\Hopper\Tests\Console;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Events\DatabaseCreated;
use Nedwors\Hopper\Events\HoppedToDatabase;
use Nedwors\Hopper\Events\HoppedToDefault;
use Nedwors\Hopper\Facades\Git;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class HopCommandTest extends TestCase
{
    /** @test */
    public function calling_hop_will_move_to_that_database_using_hopper()
    {
        Hop::partialMock()
            ->shouldReceive('to')
            ->once()
            ->withArgs(['barwhizz']);

        $this->artisan('hop barwhizz');
    }

    /** @test */
    public function calling_hop_without_an_argument_will_use_the_current_git_branch_name_as_the_database_name()
    {
        Git::partialMock()
            ->shouldReceive('current')
            ->once()
            ->andReturn('staging');

        Hop::partialMock()
            ->shouldReceive('to')
            ->once()
            ->withArgs(['staging']);

        $this->artisan('hop');
    }

    /** @test */
    public function if_git_returns_null_a_warning_is_shown()
    {
        Git::partialMock()
            ->shouldReceive('current')
            ->once()
            ->andReturn(null);

        $this->artisan('hop')->expectsOutput('Please hop on a git branch or provide a database name');
    }

    /** @test */
    public function if_hopper_fires_a_HoppedToDatabase_event_a_message_is_displayed()
    {
        Hop::swap(new FiresHoppedToDatabaseEvent);

        $this->artisan('hop test')->expectsOutput('Hopped to test');
    }

    /** @test */
    public function if_hopper_fires_a_HoppedToDefault_event_a_message_is_displayed()
    {
        Hop::swap(new FiresHoppedToDefaultEvent);

        $this->artisan('hop database')->expectsOutput('Hopped to default database: database');
    }

    /** @test */
    public function if_hopper_fires_a_DatabaseCreated_event_a_message_is_displayed()
    {
        Hop::swap(new FiresDatabaseCreatedEvent);

        $this->artisan('hop test')->expectsOutput('test was created');
    }

    /** @test */
    public function if_hopper_fires_a_DatabaseCreated_event_a_HoppedToDatabase_event_will_also_be_fired_so_both_messages_should_be_displayed()
    {
        Hop::swap(new FiresDatabaseCreatedAndHoppedToDatabaseEvents);

        $this->artisan('hop test')
            ->expectsOutput('Hopped to test')
            ->expectsOutput('test was created');
    }
}

class FiresHoppedToDatabaseEvent
{
    public function to($database)
    {
        HoppedToDatabase::dispatch($database);
    }
}

class FiresHoppedToDefaultEvent
{
    public function to($database)
    {
        HoppedToDefault::dispatch($database);
    }
}

class FiresDatabaseCreatedEvent
{
    public function to($database)
    {
        DatabaseCreated::dispatch($database);
    }
}

class FiresDatabaseCreatedAndHoppedToDatabaseEvents
{
    public function to($database)
    {
        HoppedToDatabase::dispatch($database);
        DatabaseCreated::dispatch($database);
    }
}
