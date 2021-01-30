<?php

namespace Nedwors\Hopper\Git;

class Git
{
    public static function default()
    {
        return config('hopper.default-branch');
    }

    public static function current()
    {
        return rescue(function () {
            exec('git rev-parse --abbrev-ref HEAD', $output);
            return $output[0];
        }, null, false);
    }
}
