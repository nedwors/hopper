<?php

namespace Nedwors\Hopper\Git;

class Git
{
    public function current()
    {
        return rescue(function () {
            exec('git rev-parse --abbrev-ref HEAD', $output);
            return $output[0];
        }, null, false);
    }
}
