<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class CurrentTest extends TestCase
{
    /** @test */
    public function it_accesses_the_filer_to_return_the_current_hop()
    {
        $this->mock(Filer::class)
            ->shouldReceive('currentHop')
            ->once()
            ->andReturn('foobar');

        expect(Hop::current())->toEqual('foobar');
    }
}
