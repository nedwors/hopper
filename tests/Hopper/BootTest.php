<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Nedwors\Hopper\Database;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Contracts\Engine;
use Illuminate\Support\Facades\Config;

class BootTest extends TestCase
{
    /** @test */
    public function calling_boot_will_ask_the_engine_to_boot()
    {
        $this->mock(Engine::class)
            ->shouldReceive('boot')
            ->once();

        Hop::boot();
    }
}
