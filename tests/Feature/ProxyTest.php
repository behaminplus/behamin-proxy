<?php

namespace Behamin\ServiceProxy\Tests\Feature;

use Behamin\ServiceProxy\Exceptions\ProxyException;
use Behamin\ServiceProxy\Proxy;
use Behamin\ServiceProxy\Responses\ResponseWrapper;
use Behamin\ServiceProxy\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use function PHPUnit\Framework\assertEquals;


class ProxyTest extends TestCase
{
    public function test_proxyRequest_ok()
    {
        Proxy::fake([
            config('proxy.base_url').'/test-service/api/path/1' => Http::response(['foo' => 'bar']),
        ]);

        $request = Request::create('/api/path/1');


        Proxy::request($request, 'test-service')
            ->onSuccess(function (ResponseWrapper $responseWrapper) {
                assertEquals(['foo' => 'bar'], $responseWrapper->response()->json());
                assertEquals('/test-service/api/path/1', $responseWrapper->response()->effectiveUri()->getPath());
            });
    }

    public function test_proxyRequest_fail()
    {
        Proxy::fake([
            config('proxy.base_url').'/test-service/api/path/1' => Http::response(['foo' => 'bar'], 400),
        ]);

        $request = Request::create('/api/path/1');


        Proxy::request($request,'test-service')
            ->onError(function (ProxyException $proxyException) {
                assertEquals(400, $proxyException->getCode());
                assertEquals(['foo' => 'bar'], $proxyException->responseWrapper->response()->json());
                assertEquals('/test-service/api/path/1',
                    $proxyException->responseWrapper->response()->effectiveUri()->getPath());
            });
    }

    public function test_manualProxy_ok()
    {
        Proxy::fake([
            config('proxy.base_url').'/test-service/api/path/1' => Http::response(['foo' => 'bar']),
        ]);

        Proxy::get('test-service/api/path/1')
            ->onSuccess(function (ResponseWrapper $responseWrapper) {
                assertEquals(['foo' => 'bar'], $responseWrapper->response()->json());
                assertEquals('/test-service/api/path/1', $responseWrapper->response()->effectiveUri()->getPath());
            });
    }

    public function test_manualProxy_fail()
    {
        Proxy::fake([
            config('proxy.base_url').'/test-service/api/path/1' => Http::response(['foo' => 'bar'], 400),
        ]);


        Proxy::get('test-service/api/path/1')
            ->onError(function (ProxyException $proxyException) {
                assertEquals(400, $proxyException->getCode());
                assertEquals(['foo' => 'bar'], $proxyException->responseWrapper->response()->json());
                assertEquals('/test-service/api/path/1',
                    $proxyException->responseWrapper->response()->effectiveUri()->getPath());
            });
    }
}
