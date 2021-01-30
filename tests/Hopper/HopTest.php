<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Exception;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class HopTest extends TestCase
{
    /** @test */
    public function calling_hop_will_ask_the_databaseEngine_to_use_the_given_database()
    {
        $this->mock(Filer::class);

        $this->mock(Engine::class)
            ->shouldReceive('use')
            ->once()
            ->withArgs(['foobar']);

        Hop::to('foobar');
    }

    /** @test */
    public function calling_hop_will_ask_the_filer_to_set_the_given_database()
    {
        $this->mock(Filer::class)
            ->shouldReceive('setCurrentHop')
            ->once()
            ->withArgs(['foobar']);

        Hop::to('foobar');
    }

    /** @test */
    public function if_the_engine_throws_an_exception_the_filer_is_not_asked_to_record_the_hop()
    {
        $this->mock(Engine::class)
            ->shouldReceive('use')
            ->once()
            ->andThrow(new Exception());

        $this->mock(Filer::class)
            ->shouldNotReceive('setCurrentHop');

        Hop::to('foobar');
    }
}
