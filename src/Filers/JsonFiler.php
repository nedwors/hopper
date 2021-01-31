<?php

namespace Nedwors\Hopper\Filers;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Filer;

class JsonFiler implements Filer
{
    protected $path;

    public function __construct()
    {
        $this->path = base_path('vendor/nedwors/hopper/hopper.json');
    }

    public function setCurrentHop(string $database)
    {
        File::put($this->path, json_encode(['current' => $database]));
    }

    public function currentHop(): ?string
    {
        if (!File::exists($this->path)) {
            return null;
        }

        return data_get(json_decode(File::get($this->path), true), 'current');
    }
}
