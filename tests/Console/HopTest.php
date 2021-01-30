<?php

namespace Nedwors\Hopper\Tests\Console;

use Nedwors\Hopper\Facades\Git;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class HopTest extends TestCase
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
    public function a_success_message_will_be_shown()
    {
        Hop::partialMock();

        $this->artisan('hop test')->expectsOutput('Hopped to test');
    }
}
