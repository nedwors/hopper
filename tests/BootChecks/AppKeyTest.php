<?php

namespace Nedwors\Hopper\Tests\BootChecks;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Tests\TestCase;
use Illuminate\Support\Str;
use Nedwors\Hopper\BootChecks\AppKey;

class AppKeyTest extends TestCase
{
    /** @test */
    public function if_an_app_key_exists_it_returns_true()
    {
        Config::set('app.key', Str::random());

        $check = app(AppKey::class);

        expect($check->check())->toBeTrue();
    }

    /** @test */
    public function if_an_app_key_doesnt_exist_it_returns_false()
    {
        Config::set('app.key', null);

        $check = app(AppKey::class);

        expect($check->check())->toBeFalse();
    }
}
