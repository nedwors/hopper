<?php

namespace Nedwors\Hopper\Tests\Git;

use Nedwors\Hopper\Facades\Git;
use Nedwors\Hopper\Tests\TestCase;

class GitTest extends TestCase
{
    /** @test */
    public function current_will_return_the_current_git_branch()
    {
        exec('git branch --show-current', $output);
        $actualBranch = $output[0];

        $branch = Git::current();

        expect($branch)->toEqual($actualBranch);
    }
}
