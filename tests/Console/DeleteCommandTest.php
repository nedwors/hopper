<?php

namespace Nedwors\Hopper\Tests\Console;

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
            ->withArgs(['hello-world'])
            ->andReturn(rand(1, 2) == 1);

        $this->artisan('hop:delete hello-world');
    }

    /** @test */
    public function when_the_database_is_deleted_a_message_is_displayed()
    {
        Hop::partialMock()
            ->shouldReceive('delete')
            ->andReturn(true);

        $this->artisan('hop:delete hello-world')
            ->expectsOutput('Successfully deleted hello-world');
    }

    /** @test */
    public function when_the_database_is_not_deleted_an_error_is_displayed()
    {
        Hop::partialMock()
            ->shouldReceive('delete')
            ->andReturn(false);

        $this->artisan('hop:delete hello-world')
            ->expectsOutput('hello-world was not deleted');
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
