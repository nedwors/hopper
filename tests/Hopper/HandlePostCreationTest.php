<?php

namespace Nedwors\Hopper\Tests\Hopper;

use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

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

    /** @test */
    public function the_steps_are_run_in_order_of_declaration()
    {
        $tracker = 1;

        Config::set('hopper.post-creation-steps', [
            'hopper-test',
            function () use (&$tracker) {
                $tracker++;
                expect($tracker)->toEqual(3);
            },
        ]);

        Artisan::command('hopper-test', function () use (&$tracker) {
            $tracker++;
            expect($tracker)->toEqual(2);
        });

        Hop::handlePostCreation();
    }
}
