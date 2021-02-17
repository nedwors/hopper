<?php

namespace Nedwors\Hopper\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DatabaseNotDeleted
{
    const DOES_NOT_EXIST = 'does-not-exist';
    const DEFAULT = 'default';

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $name;
    public $reason;

    public function __construct($name, $reason)
    {
        $this->name = $name;
        $this->reason = $reason;
    }
}
