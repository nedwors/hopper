<?php

namespace Nedwors\Hopper;

use Nedwors\Hopper\Git\Git;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Filers\JsonFiler;
use Nedwors\Hopper\Console\HopCommand;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Console\DeleteCommand;
use Nedwors\Hopper\Console\CurrentCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HopperServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('hopper')
            ->hasConfigFile()
            ->hasCommands([CurrentCommand::class, DeleteCommand::class, HopCommand::class]);
    }

    public function packageRegistered()
    {
        $this->app->bind(Filer::class, JsonFiler::class);
        $this->app->bind(Connection::class, $this->getConnectionDriver());
        $this->app->bind(Engine::class, Engines\Engine::class);

        $this->app->singleton('hopper', fn() => new Hopper(app(Engine::class)));
        $this->app->singleton('hopper-git', fn() => new Git);
    }

    protected function getConnectionDriver()
    {
        return config('hopper.connections')[config('database.default', 'sqlite')]['driver'];
    }

    public function packageBooted()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        if (!config('app.key')) {
            return;
        }

        if (config('app.env') !== "local") {
            return;
        }

        Hop::boot();
    }
}
