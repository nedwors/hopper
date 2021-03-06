<?php

namespace Nedwors\Hopper\Tests\Console;

use Nedwors\Hopper\Database;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Exceptions\NoConnectionException;

class CurrentCommandTest extends TestCase
{
    /** @test */
    public function it_returns_the_name_of_the_current_database_wired_up_by_hopper()
    {
        Hop::shouldReceive('current')
            ->once()
            ->andReturn(new Database('foobar', '', 'sqlite'));

        $this->artisan('hop:current')
             ->expectsOutput('Currently using foobar');
    }

    /** @test */
    public function if_there_is_no_current_hop_a_message_is_outputted()
    {
        Hop::shouldReceive('current')
            ->once()
            ->andReturn(null);

        $this->artisan('hop:current')
             ->expectsOutput('Currently using the default database');
    }

    /** @test */
    public function if_a_NoConnectionException_is_thrown_a_safe_message_is_displayed()
    {
        Hop::swap(new ThrowsCurrentNoConnectionException);

        $this->artisan('hop:current')
            ->expectsOutput('Sorry, your database connection is not currently supported by Hopper');
    }
}

class ThrowsCurrentNoConnectionException
{
    public function current()
    {
        throw new NoConnectionException;
    }
}
