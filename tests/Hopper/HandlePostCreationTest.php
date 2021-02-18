<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;

class HandlePostCreationTest extends TestCase
{
    /** @test */
    public function if_the_postCreationSteps_are_strings_these_are_run_as_artisan_commands()
    {
        Artisan::partialMock()
            ->shouldReceive('call')
            ->once()
            ->withArgs(['migrate:fresh --seed']);

        Config::set('hopper.post-creation-steps', [
            'migrate:fresh --seed'
        ]);

        Hop::handlePostCreation();
    }

    /** @test */
    public function if_the_postCreationSteps_are_closures_these_are_executed()
    {
        Config::set('hopper.post-creation-steps', [
            fn() => $this->assertTrue(true)
        ]);

        Hop::handlePostCreation();
    }

    /** @test */
    public function a_mixture_of_commands_and_closures_can_be_passed()
    {
        Artisan::partialMock()
            ->shouldReceive('call')
            ->once()
            ->withArgs(['migrate:fresh --seed']);

        Config::set('hopper.post-creation-steps', [
            fn() => $this->assertTrue(true),
            'migrate:fresh --seed'
        ]);

        Hop::handlePostCreation();
    }
}
