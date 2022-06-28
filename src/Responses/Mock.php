<?php

namespace Behamin\ServiceProxy\Responses;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Mock
{
    public static function fakeResponse($jsonPath): Response
    {
        $mockDirectory = base_path(). DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR;
        $jsonFile = file_get_contents($mockDirectory.$jsonPath);
        $json = json_decode($jsonFile, true, 512, JSON_THROW_ON_ERROR);
        $path = Str::uuid()->toString();
        Http::fake([
            $path => $json
        ]);
        return Http::get($path);
    }
}
