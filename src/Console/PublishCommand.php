<?php

namespace Nedwors\Hopper\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'hop:publish';

    protected $description = 'Publish the hopper config file to your application';

    public function handle()
    {
        $this->call('vendor:publish', ['--tag' => 'hopper-config']);
    }
}
