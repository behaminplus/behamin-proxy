<?php

namespace Behamin\ServiceProxy\Tests\Feature;

use Behamin\ServiceProxy\Proxy;
use Behamin\ServiceProxy\Tests\TestCase;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class AsyncProxyTest extends TestCase
{
    public function test_async_request(): void
    {
        Proxy::fake([
            'test.com/api/path' => Http::response([
                'data' => [
                    'foo' => 'bar'
                ],
                'message' => null
            ]),
            'example.com/api/path' => Http::response([
                'data' => null,
                'message' => null,
                'error' => [
                    'message' => 'error message',
                    'errors' => null
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY),
        ]);

        $responses = Proxy::pool(function (Pool $pool) {
            return [
                $pool->get('test.com/api/path'),
                $pool->get('example.com/api/path'),
            ];
        });

        $this->assertEquals(Response::HTTP_OK, $responses[0]->status());
        $this->assertEquals('bar', $responses[0]->data()['foo']);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $responses[1]->status());
        $this->assertEquals('error message', $responses[1]->error()['message']);
    }
}
