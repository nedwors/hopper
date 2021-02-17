<?php

namespace Nedwors\Hopper\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HoppedToDefault
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
