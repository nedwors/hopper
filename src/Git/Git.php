<?php

namespace Nedwors\Hopper\Git;

class Git
{
    public function current()
    {
        return rescue(function () {
            exec('git branch --show-current', $output);
            return $output[0];
        }, null, false);
    }
}
