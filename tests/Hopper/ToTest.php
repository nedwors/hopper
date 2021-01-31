<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

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
}
