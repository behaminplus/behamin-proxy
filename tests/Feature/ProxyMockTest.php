<?php

namespace Behamin\ServiceProxy\Tests\Feature;

use Behamin\ServiceProxy\Exceptions\ProxyException;
use Behamin\ServiceProxy\Proxy;
use Behamin\ServiceProxy\Responses\ProxyResponse;
use Behamin\ServiceProxy\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use function PHPUnit\Framework\assertEquals;

class ProxyMockTest extends TestCase
{
    private string $jsonPathBackStepUpFromLaravel = '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
    '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
    'tests'.DIRECTORY_SEPARATOR.'mock'.DIRECTORY_SEPARATOR.'test.json';

    private string $jsonPath = __DIR__ . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
    'tests'.DIRECTORY_SEPARATOR.'mock'.DIRECTORY_SEPARATOR.'test.json';

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testMockIsWorking(): void
    {
        $response = Proxy::mock($this->jsonPathBackStepUpFromLaravel)->get('test');
        $jsonFile = file_get_contents($this->jsonPath);
        $json = json_decode($jsonFile, true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals($response->json(), $json);
    }
}
