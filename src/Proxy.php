<?php

namespace Behamin\ServiceProxy;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * Class Proxy
 * @package Proxy
 *
 * @method static \Illuminate\Http\Client\Factory fake($callback = null)
 * @method static \Behamin\ServiceProxy\Http accept(string $contentType)
 * @method static \Behamin\ServiceProxy\Http acceptJson()
 * @method static \Behamin\ServiceProxy\Http asForm()
 * @method static \Behamin\ServiceProxy\Http asJson()
 * @method static \Behamin\ServiceProxy\Http asMultipart()
 * @method static \Behamin\ServiceProxy\Http async()
 * @method static \Behamin\ServiceProxy\Http attach(string|array $name, string $contents = '', string|null $filename = null, array $headers = [])
 * @method static \Behamin\ServiceProxy\Http baseUrl(string $url)
 * @method static \Behamin\ServiceProxy\Http beforeSending(callable $callback)
 * @method static \Behamin\ServiceProxy\Http bodyFormat(string $format)
 * @method static \Behamin\ServiceProxy\Http contentType(string $contentType)
 * @method static \Behamin\ServiceProxy\Http dd()
 * @method static \Behamin\ServiceProxy\Http dump()
 * @method static \Behamin\ServiceProxy\Http retry(int $times, int $sleep = 0)
 * @method static \Behamin\ServiceProxy\Http sink(string|resource $to)
 * @method static \Behamin\ServiceProxy\Http stub(callable $callback)
 * @method static \Behamin\ServiceProxy\Http timeout(int $seconds)
 * @method static \Behamin\ServiceProxy\Http withBasicAuth(string $username, string $password)
 * @method static \Behamin\ServiceProxy\Http withBody(resource|string $content, string $contentType)
 * @method static \Behamin\ServiceProxy\Http withCookies(array $cookies, string $domain)
 * @method static \Behamin\ServiceProxy\Http withDigestAuth(string $username, string $password)
 * @method static \Behamin\ServiceProxy\Http withHeaders(array $headers)
 * @method static \Behamin\ServiceProxy\Http withMiddleware(callable $middleware)
 * @method static \Behamin\ServiceProxy\Http withOptions(array $options)
 * @method static \Behamin\ServiceProxy\Http withToken(string $token, string $type = 'Bearer')
 * @method static \Behamin\ServiceProxy\Http withUserAgent(string $userAgent)
 * @method static \Behamin\ServiceProxy\Http withoutRedirecting()
 * @method static \Behamin\ServiceProxy\Http withoutVerifying()
 * @method static array pool(callable $callback)
 * @method static \Behamin\ServiceProxy\Responses\ResponseWrapper request(Request $request, string $service)
 * @method static \Illuminate\Http\Client\Response delete(string $url, array $data = [])
 * @method static \Behamin\ServiceProxy\Responses\ResponseWrapper get(string $url, array|string|null $query = null)
 * @method static \Behamin\ServiceProxy\Responses\ResponseWrapper head(string $url, array|string|null $query = null)
 * @method static \Behamin\ServiceProxy\Responses\ResponseWrapper patch(string $url, array $data = [])
 * @method static \Behamin\ServiceProxy\Responses\ResponseWrapper post(string $url, array $data = [])
 * @method static \Behamin\ServiceProxy\Responses\ResponseWrapper put(string $url, array $data = [])
 * @method static \Behamin\ServiceProxy\Responses\ResponseWrapper send(string $method, string $url, array $options = [])
 * @method static \Illuminate\Http\Client\ResponseSequence fakeSequence(string $urlPattern = '*')
 * @method static void assertSent(callable $callback)
 * @method static void assertNotSent(callable $callback)
 * @method static void assertNothingSent()
 * @method static void assertSentCount(int $count)
 * @method static void assertSequencesAreEmpty()
 */
class Proxy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Http::class;
    }
}
