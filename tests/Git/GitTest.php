<?php

namespace Nedwors\Hopper\Tests\Git;

use Illuminate\Support\Facades\Config;
use Nedwors\Hopper\Git\Git;
use Nedwors\Hopper\Tests\TestCase;

class GitTest extends TestCase
{
    /** @test */
    public function current_will_return_the_current_git_branch()
    {
        exec('git rev-parse --abbrev-ref HEAD', $output);
        $actualBranch = $output[0];

        $branch = Git::current();

        expect($branch)->toEqual($actualBranch);
    }

    /** @test */
    public function default_will_return_the_configured_default_branch_name()
    {
        Config::set('hopper.default-branch', 'main');

        expect(Git::default())->toEqual('main');
    }

    /** @test */
    public function if_the_setting_is_null_null_is_returned()
    {
        Config::set('hopper.default-branch', null);

        expect(Git::default())->toBeNull();
    }
}
