<?php

namespace Nedwors\Hopper\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class DatabaseCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
