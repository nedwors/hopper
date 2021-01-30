<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class DeleteTest extends TestCase
{
    /** @test */
    public function delete_will_ask_the_engine_to_delete_the_given_database()
    {
        $this->mock(Engine::class)
            ->shouldReceive('delete')
            ->once()
            ->withArgs(['hello-world'])
            ->andReturn($deleted = rand(1,2) == 1);

        expect(Hop::delete('hello-world'))->toEqual($deleted);
    }
}
