<?php

namespace Behamin\ServiceProxy\Responses;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Mock
{
    public static function fakeResponse($jsonPath): Response
    {
        $mockDirectory = base_path(). DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR;
        $jsonFile = file_get_contents($mockDirectory.$jsonPath);
        $json = json_decode($jsonFile, true, 512, JSON_THROW_ON_ERROR);
        Http::fake([
            'test' => $json
        ]);
        return Http::get('test');
    }
}
