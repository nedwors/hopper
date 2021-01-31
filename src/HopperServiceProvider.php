<?php

namespace Nedwors\Hopper;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Nedwors\Hopper\Connections\Sqlite;
use Nedwors\Hopper\Console\CurrentCommand;
use Nedwors\Hopper\Console\DeleteCommand;
use Nedwors\Hopper\Console\HopCommand;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Filers\JsonFiler;
use Nedwors\Hopper\Git\Git;

class HopperServiceProvider extends ServiceProvider
{
    protected static $connections = [
        'sqlite' => Sqlite::class
    ];

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'hopper');

        $this->app->bind(Filer::class, JsonFiler::class);
        $this->app->bind(Connection::class, static::$connections[config('hopper.connection')]);
        $this->app->bind(Engine::class, Engines\Engine::class);

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

        if (!File::exists(database_path(config('hopper.drivers.sqlite.database-path')))) {
            File::makeDirectory(database_path(config('hopper.drivers.sqlite.database-path')));
        }

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

            if ($this->canBoot()) {
                Hop::boot();
            }
        }
    }

    protected function canBoot()
    {
        if (!env("APP_KEY")) {
            return false;
        }

        if (config('app.env') !== "production") {
            return false;
        }

        return true;
    }
}
