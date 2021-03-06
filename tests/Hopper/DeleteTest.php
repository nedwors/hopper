<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Engine;

class DeleteTest extends TestCase
{
    /** @test */
    public function delete_will_ask_the_engine_to_delete_the_given_database()
    {
        $this->mock(Engine::class)
            ->shouldReceive('delete')
            ->once()
            ->withArgs(['hello-world']);

        Hop::delete('hello-world');
    }
}
