<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Nedwors\Hopper\Database;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Engine;

class CurrentTest extends TestCase
{
    /** @test */
    public function it_accesses_the_engine_to_return_the_current_database()
    {
        $this->mock(Engine::class)
            ->shouldReceive('current')
            ->once()
            ->andReturn(new Database('foobar', 'foobar.sqlite', 'sqlite'));

        $database = Hop::current();
        expect($database->name)->toEqual('foobar');
        expect($database->db_database)->toEqual('foobar.sqlite');
    }

    /** @test */
    public function it_returns_null_if_the_engine_does()
    {
        $this->mock(Engine::class)
            ->shouldReceive('current')
            ->once()
            ->andReturn(null);

        expect(Hop::current())->toBeNull();
    }
}
