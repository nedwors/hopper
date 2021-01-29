<?php

namespace Nedwors\Hopper\Tests\Filers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Filers\JsonFiler;
use Nedwors\Hopper\Interfaces\Filer;
use Nedwors\Hopper\Tests\TestCase;

class JsonFilerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->swap(Filer::class, app(JsonFiler::class));
    }

    /** @test */
    public function when_setCurrentHop_is_called_the_hopperJson_file_is_updated_with_the_new_database_name()
    {
        File::partialMock()
            ->shouldReceive('put')
            ->once()
            ->withArgs(function ($jsonPath, $database) {
                expect($jsonPath)->toEqual('./hopper.json');
                expect($database)->toEqual(json_encode(['current' => 'database.sqlite']));
                return true;
            });

        app(Filer::class)->setCurrentHop('database.sqlite');
    }

    /** @test */
    public function currentHop_returns_the_value_in_the_hopperJson_file()
    {
        File::partialMock()
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('get')
            ->once()
            ->withArgs(['./hopper.json'])
            ->andReturn(json_encode(['current' => 'foobar.sqlite']));

        $current = app(Filer::class)->currentHop();

        expect($current)->toEqual('foobar.sqlite');
    }

    /**
     * @dataProvider invalidCurrentHopDataProvider
     * @test */
    public function currentHop_returns_null_if_no_value_is_accessible_in_the_hopperJson_file($currentHop)
    {
        File::partialMock()
            ->shouldReceive('exists')
            ->andReturn(true)
            ->shouldReceive('get')
            ->once()
            ->withArgs(['./hopper.json'])
            ->andReturn(json_encode($currentHop));

        $current = app(Filer::class)->currentHop();

        expect($current)->toBeNull();
    }

    /** @test */
    public function if_the_json_file_does_not_exist_null_is_returned()
    {
        File::partialMock()
            ->shouldReceive('exists')
            ->andReturn(false)
            ->shouldReceive('get')
            ->withArgs(['./hopper.json'])
            ->andThrow(new FileNotFoundException());

        $current = app(Filer::class)->currentHop();

        expect($current)->toBeNull();
    }

    public function invalidCurrentHopDataProvider()
    {
        return [
            ['current' => null],
            [null],
            ['current' => '']
        ];
    }
}
