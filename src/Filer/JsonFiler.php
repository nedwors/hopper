<?php

namespace Nedwors\Hopper\Filer;

use Illuminate\Support\Facades\File;
use Nedwors\Hopper\Contracts\Filer;

class JsonFiler implements Filer
{
    const JSON_PATH = './hopper.json';

    public function setCurrentHop(string $database)
    {
        File::put(self::JSON_PATH, json_encode(['current' => $database]));
    }

    public function currentHop(): ?string
    {
        return data_get(json_decode(File::get(self::JSON_PATH), true), 'current');
    }
}
