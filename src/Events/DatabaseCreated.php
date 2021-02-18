<?php

namespace Nedwors\Hopper\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DatabaseCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
