<?php

namespace Nedwors\Hopper;

use Nedwors\Hopper\Console\CurrentCommand;
use Nedwors\Hopper\Console\DeleteCommand;
use Nedwors\Hopper\Console\HopCommand;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Filers\JsonFiler;
use Nedwors\Hopper\Git\Git;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HopperServiceProvider extends PackageServiceProvider
{
    public $bindings = [
        Filer::class => JsonFiler::class,
        Engine::class => Engines\Engine::class,
    ];

    public $singletons = [
        'hopper' => Hopper::class,
        'hopper-git' => Git::class,
    ];

    public function configurePackage(Package $package): void
    {
        $package->name('hopper')
            ->hasConfigFile()
            ->hasCommands([CurrentCommand::class, DeleteCommand::class, HopCommand::class]);
    }

    public function packageRegistered()
    {
        $connectionDriver = config('hopper.connections')[config('database.default', 'sqlite')]['driver'];
        $this->app->bind(Connection::class, $connectionDriver);
    }

    public function packageBooted()
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        Hop::boot();
    }
}
