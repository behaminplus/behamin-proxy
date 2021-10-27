<?php

namespace Behamin\ServiceProxy\Tests\Feature;

use Behamin\ServiceProxy\Exceptions\ProxyException;
use Behamin\ServiceProxy\Proxy;
use Behamin\ServiceProxy\Response\ResponseWrapper;
use Behamin\ServiceProxy\Tests\TestCase;
use Illuminate\Http\Request;


class ExampleTest extends TestCase
{
    public function test_example()
    {
        Proxy::service('call-service')
            ->withHeaders(['X-PROXY-TOKEN' => '11212', 'app-id' => 0])
            ->acceptJson()
            ->get('api/calls/278111')
            ->onSuccess(function (ResponseWrapper $responseWrapper) {
                dump($responseWrapper->data());
            });


        $request = Request::create('/api/admin/calls/278111');
        $request->headers->set('Authorization', '121212');
        $request->headers->set('Department', 4);

        Proxy::service('behyar-service')
            ->request($request)
            ->onSuccess(function (ResponseWrapper $responseWrapper) {
                dump($responseWrapper->data());
            })->onError(function (ProxyException $exception) {
                dump($exception);
            });
    }
}
