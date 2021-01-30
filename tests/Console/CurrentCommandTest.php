<?php

namespace Nedwors\Hopper\Tests\Console;

use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Database;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class CurrentCommandTest extends TestCase
{
    /** @test */
    public function it_returns_the_name_of_the_current_database_wired_up_by_hopper()
    {
        Hop::shouldReceive('current')
            ->once()
            ->andReturn(new Database('foobar', '', 'sqlite'));

        $this->artisan('hop:current')
             ->expectsOutput('foobar');
    }

    /** @test */
    public function if_there_is_no_current_hop_a_message_is_outputted()
    {
        Hop::shouldReceive('current')
            ->once()
            ->andReturn(null);

        $this->artisan('hop:current')
             ->expectsOutput('There is no current hopper db...');
    }
}
