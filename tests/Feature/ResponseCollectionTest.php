<?php

namespace Behamin\ServiceProxy\Tests\Feature;

use Behamin\ServiceProxy\Proxy;
use Behamin\ServiceProxy\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ResponseCollectionTest extends TestCase
{
    public function testResponseItemsCanBeCollected(): void
    {
        $items = [
            [
                'id' => 1,
                'name' => 'test'
            ],
            [
                'id' => 2,
                'name' => 'hello'
            ],
        ];

        Proxy::fake([
            'https://example.com/api' => Http::response([
                'data' => [
                    'items' => $items
                ]
            ]),
        ]);

        $response = Proxy::get('https://example.com/api');

        $collection = $response->collect();

        $this->assertCount(count($items), $collection);
        $this->assertEquals('test', $collection->where('id', 1)->first()['name']);
        $this->assertEquals('hello', $collection->where('id', 2)->first()['name']);
    }

    public function testResponseDataCanBeCollected(): void
    {
        $data = [
            [
                'id' => 1,
                'name' => 'test'
            ],
            [
                'id' => 2,
                'name' => 'hello'
            ],
        ];

        Proxy::fake([
            'https://example.com/api' => Http::response([
                'data' => $data
            ]),
        ]);

        $response = Proxy::get('https://example.com/api');

        $collection = $response->collect();

        $this->assertCount(count($data), $collection);
        $this->assertEquals('test', $collection->where('id', 1)->first()['name']);
        $this->assertEquals('hello', $collection->where('id', 2)->first()['name']);
    }

    public function testNullResponseDataCanBeCollected(): void
    {
        Proxy::fake([
            'https://example.com/api' => Http::response([
                'data' => null,
                'message' => 'error'
            ]),
        ]);

        $response = Proxy::get('https://example.com/api');

        $collection = $response->collect();

        $this->assertEmpty($collection->all());
    }
}
