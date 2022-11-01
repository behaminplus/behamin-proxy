<?php

namespace Behamin\ServiceProxy\Tests\Feature;

use Behamin\ServiceProxy\Exceptions\ProxyException;
use Behamin\ServiceProxy\Proxy;
use Behamin\ServiceProxy\Responses\ProxyResponse;
use Behamin\ServiceProxy\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use function PHPUnit\Framework\assertEquals;

class ProxyTest extends TestCase
{
    public function testSuccessfulProxyRequest(): void
    {
        Proxy::fake([
            config('proxy.base_url').'/test-service/api/path/1' => Http::response(['foo' => 'bar']),
        ]);

        $request = Request::create('/api/path/1');

        Proxy::request($request, 'test-service')
            ->onSuccess(function (ProxyResponse $responseWrapper) {
                assertEquals(['foo' => 'bar'], $responseWrapper->response()->json());
                assertEquals('/test-service/api/path/1', $responseWrapper->response()->effectiveUri()->getPath());
            });
    }

    public function testFailedProxyRequest(): void
    {
        Proxy::fake([
            config('proxy.base_url').'/test-service/api/path/1' => Http::response(['foo' => 'bar'], 400),
        ]);

        $request = Request::create('/api/path/1');

        Proxy::request($request, 'test-service')
            ->onError(function (ProxyException $proxyException) {
                assertEquals(400, $proxyException->getCode());
                assertEquals(['foo' => 'bar'], $proxyException->proxyResponse->response()->json());
                assertEquals('/test-service/api/path/1',
                    $proxyException->proxyResponse->response()->effectiveUri()->getPath());
            });
    }

    public function testSuccessfulDomainChange(): void
    {
        $domain = 'http://sub.domain.example';
        Proxy::fake([
            $domain.'/test-service/api/path/1' => Http::response(['foo' => 'bar']),
        ]);

        Proxy::domain($domain)->get('test-service/api/path/1')
            ->onSuccess(function (ProxyResponse $responseWrapper) use ($domain) {
                $effectiveUri = $responseWrapper->response()->effectiveUri();
                assertEquals($domain,$effectiveUri->getScheme() . '://'. $effectiveUri->getHost() );
            })->throw();
    }

    public function testSuccessfulManualRequest(): void
    {
        Proxy::fake([
            config('proxy.base_url').'/test-service/api/path/1' => Http::response(['foo' => 'bar']),
        ]);

        Proxy::get('test-service/api/path/1')
            ->onSuccess(function (ProxyResponse $responseWrapper) {
                assertEquals(['foo' => 'bar'], $responseWrapper->response()->json());
                assertEquals('/test-service/api/path/1', $responseWrapper->response()->effectiveUri()->getPath());
            });
    }

    public function testFailedManualRequest(): void
    {
        Proxy::fake([
            config('proxy.base_url').'/test-service/api/path/1' => Http::response(['foo' => 'bar'], 400),
        ]);

        Proxy::get('test-service/api/path/1')
            ->onError(function (ProxyException $proxyException) {
                assertEquals(400, $proxyException->getCode());
                assertEquals(['foo' => 'bar'], $proxyException->proxyResponse->response()->json());
                assertEquals('/test-service/api/path/1',
                    $proxyException->proxyResponse->response()->effectiveUri()->getPath());
            });
    }

    public function testResponseData(): void
    {
        Proxy::fake([
            config('proxy.base_url').'/test-service/api/path/1' => Http::response([
                'data' => ['foo' => 'bar']
            ]),
        ]);

        $response = Proxy::get('test-service/api/path/1');

        $this->assertEquals(['foo' => 'bar'], $response->data());
        $this->assertEquals('bar', $response->data('foo'));
    }
}
