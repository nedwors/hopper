<?php

namespace Nedwors\Hopper;

use Illuminate\Support\ServiceProvider;
use Nedwors\Hopper\Console\CurrentCommand;
use Nedwors\Hopper\Console\DeleteCommand;
use Nedwors\Hopper\Console\HopCommand;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Engines\SqliteEngine;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Filers\JsonFiler;
use Nedwors\Hopper\Git\Git;

class HopperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'hopper');

        $this->app->bind(Filer::class, JsonFiler::class);

        $driver = config('hopper.driver');
        $this->app->bind(Engine::class, config("hopper.drivers.$driver.engine"));

        $this->app->singleton('hopper', fn() => new Hopper(app(Engine::class)));
        $this->app->singleton('hopper-git', fn() => new Git);
    }

    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'hopper');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'hopper');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('hopper.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/hopper'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/hopper'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/hopper'),
            ], 'lang');*/

            $this->commands([
                CurrentCommand::class,
                DeleteCommand::class,
                HopCommand::class
            ]);

            if (config('app.env') !== "production" && env("APP_KEY")) {
                Hop::boot();
            }
        }
    }
}
