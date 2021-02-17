<?php

namespace Nedwors\Hopper\Tests\Console;

use Nedwors\Hopper\Events\DatabaseDeleted;
use Nedwors\Hopper\Events\DatabaseNotDeleted;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class DeleteCommandTest extends TestCase
{
    /** @test */
    public function it_deletes_the_given_database_name_using_hopper()
    {
        Hop::partialMock()
            ->shouldReceive('delete')
            ->once()
            ->withArgs(['hello-world']);

        $this->artisan('hop:delete hello-world');
    }

    /** @test */
    public function when_a_DatabaseDeleted_event_is_fired_a_message_is_displayed()
    {
        Hop::swap(new FiresDatabaseDeletedEvent);

        $this->artisan('hop:delete hello-world')
            ->expectsOutput('Successfully deleted hello-world');
    }

    /** @test */
    public function when_a_DatabaseNotDeleted_event_is_fired_because_the_database_does_not_exist_a_message_is_displayed()
    {
        Hop::swap(new FiresDatabaseNotDeletedEvent(DatabaseNotDeleted::DOES_NOT_EXIST));

        $this->artisan('hop:delete hello-world')
            ->expectsOutput('hello-world does not exist, so it was not deleted');
    }

    /** @test */
    public function when_a_DatabaseNotDeleted_event_is_fired_because_the_database_is_the_default_a_message_is_displayed()
    {
        Hop::swap(new FiresDatabaseNotDeletedEvent(DatabaseNotDeleted::DEFAULT));

        $this->artisan('hop:delete hello-world')
            ->expectsOutput('hello-world is the default database, so it was not deleted');
    }

    /** @test */
    public function if_no_database_name_is_given_a_warning_is_displayed_and_hopper_is_not_accessed()
    {
        Hop::partialMock()
            ->shouldNotReceive('delete');

        $this->artisan('hop:delete')
            ->expectsOutput('Please provide a database to be deleted');
    }
}

class FiresDatabaseDeletedEvent
{
    public function delete($database)
    {
        DatabaseDeleted::dispatch($database);
    }
}
class FiresDatabaseNotDeletedEvent
{
    protected $reason;

    public function __construct($reason = DatabaseNotDeleted::DOES_NOT_EXIST)
    {
        $this->reason = $reason;
    }

    public function delete($database)
    {
        DatabaseNotDeleted::dispatch($database, $this->reason);
    }
}
