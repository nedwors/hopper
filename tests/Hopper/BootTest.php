<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Contracts\BootCheck;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;
use Nedwors\Hopper\Contracts\Engine;

class BootTest extends TestCase
{
    /** @test */
    public function calling_boot_will_ask_the_engine_to_boot_if_all_the_bootChecks_pass()
    {
        Config::set('hopper.boot-checks', [
            TrueBootCheck::class,
            TrueBootCheck::class,
            TrueBootCheck::class
        ]);

        $this->mock(Engine::class)
            ->shouldReceive('boot')
            ->once();

        Hop::boot();
    }

    /** @test */
    public function calling_boot_will_not_ask_the_engine_to_boot_if_all_the_bootChecks_fail()
    {
        Config::set('hopper.boot-checks', [
            FalseBootCheck::class,
            FalseBootCheck::class,
            FalseBootCheck::class
        ]);

        $this->mock(Engine::class)
            ->shouldNotReceive('boot');

        Hop::boot();
    }

    /** @test */
    public function calling_boot_will_not_ask_the_engine_to_boot_if_just_one_bootCheck_fail()
    {
        Config::set('hopper.boot-checks', [
            TrueBootCheck::class,
            TrueBootCheck::class,
            FalseBootCheck::class
        ]);

        $this->mock(Engine::class)
            ->shouldNotReceive('boot');

        Hop::boot();
    }

    /** @test */
    public function if_there_are_no_checks_configured_calling_boot_will_ask_the_engine_to_boot()
    {
        Config::set('hopper.boot-checks', []);

        $this->mock(Engine::class)
            ->shouldReceive('boot')
            ->once();

        Hop::boot();
    }
}

class TrueBootCheck implements BootCheck
{
    public function check(): bool
    {
        return true;
    }
}

class FalseBootCheck implements BootCheck
{
    public function check(): bool
    {
        return false;
    }
}
