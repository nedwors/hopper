<?php

namespace Nedwors\Hopper;

use Nedwors\Hopper\Git\Git;
use Nedwors\Hopper\Facades\Hop;
use Nedwors\Hopper\Contracts\Filer;
use Nedwors\Hopper\Contracts\Engine;
use Nedwors\Hopper\Filers\JsonFiler;
use Nedwors\Hopper\Console\HopCommand;
use Spatie\LaravelPackageTools\Package;
use Nedwors\Hopper\Contracts\Connection;
use Nedwors\Hopper\Console\DeleteCommand;
use Nedwors\Hopper\Console\CurrentCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HopperServiceProvider extends PackageServiceProvider
{
    protected $isUsingSupportedConnection;

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
        $supportedConnections = config('hopper.connections');
        $connection = config('database.default');

        $this->isUsingSupportedConnection = in_array($connection, array_keys($supportedConnections));

        if (!$this->isUsingSupportedConnection) {
            return;
        }

        $this->app->bind(Connection::class, $supportedConnections[$connection]['driver']);
    }

    public function packageBooted()
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        if (!$this->isUsingSupportedConnection) {
            return;
        }

        Hop::boot();
    }
}
