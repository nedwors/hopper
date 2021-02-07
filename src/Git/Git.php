<?php

namespace Nedwors\Hopper\Git;

class Git
{
    public function current()
    {
        exec('git branch --show-current', $output);
        return data_get($output, 0);
    }

    public function default()
    {
        return config('hopper.default-branch') ?? 'main';
    }
}
