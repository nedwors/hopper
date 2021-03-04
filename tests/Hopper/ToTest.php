<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Engine;

class ToTest extends TestCase
{
    /** @test */
    public function calling_hop_will_ask_the_databaseEngine_to_use_the_given_database()
    {
        $this->mock(Engine::class)
            ->shouldReceive('use')
            ->once()
            ->withArgs(['foobar']);

        Hop::to('foobar');
    }

    /** @test */
    public function calling_hop_with_the_default_option_will_pass_the_engine_default_option_to_the_engine()
    {
        $this->mock(Engine::class)
            ->shouldReceive('use')
            ->once()
            ->withArgs([Engine::DEFAULT]);

        Hop::to(Hop::getFacadeRoot()::DEFAULT);
    }
}
