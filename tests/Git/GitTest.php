<?php

namespace Nedwors\Hopper\Tests\Git;

use Nedwors\Hopper\Facades\Git;
use Nedwors\Hopper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class GitTest extends TestCase
{
    /** @test */
    public function current_will_return_the_current_git_branch()
    {
        exec('git branch --show-current', $output);
        $actualBranch = data_get($output, 0);

        $branch = Git::current();

        expect($branch)->toEqual($actualBranch);
    }

    /** @test */
    public function default_will_return_the_value_from_config_default_branch()
    {
        Config::set('hopper.default-branch', 'foobar');

        expect(Git::default())->toEqual('foobar');
    }

    /** @test */
    public function default_will_default_to_main_if_there_is_no_value_in_config()
    {
        Config::set('hopper.default-branch', null);

        expect(Git::default())->toEqual('main');
    }
}
