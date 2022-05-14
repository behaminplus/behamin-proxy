<?php

namespace Behamin\ServiceProxy;

use Behamin\ServiceProxy\Responses\ProxyResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\ResponseSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * Class Proxy
 * @package Proxy
 *
 * @method static Factory fake($callback = null)
 * @method static Http accept(string $contentType)
 * @method static Http acceptJson()
 * @method static Http mock(string $jsonPath)
 * @method static Http asForm()
 * @method static Http asJson()
 * @method static Http asMultipart()
 * @method static Http async()
 * @method static Http attach(string|array $name, string $contents = '', string|null $filename = null, array $headers = [])
 * @method static Http baseUrl(string $url)
 * @method static Http beforeSending(callable $callback)
 * @method static Http bodyFormat(string $format)
 * @method static Http contentType(string $contentType)
 * @method static Http dd()
 * @method static Http dump()
 * @method static Http retry(int $times, int $sleep = 0)
 * @method static Http sink(string|resource $to)
 * @method static Http stub(callable $callback)
 * @method static Http timeout(int $seconds)
 * @method static Http withBasicAuth(string $username, string $password)
 * @method static Http withBody(resource|string $content, string $contentType)
 * @method static Http withCookies(array $cookies, string $domain)
 * @method static Http withDigestAuth(string $username, string $password)
 * @method static Http withHeaders(array $headers)
 * @method static Http withMiddleware(callable $middleware)
 * @method static Http withOptions(array $options)
 * @method static Http withToken(string $token, string $type = 'Bearer')
 * @method static Http withUserAgent(string $userAgent)
 * @method static Http withoutRedirecting()
 * @method static Http withoutVerifying()
 * @method static array pool(callable $callback)
 * @method static ProxyResponse request(Request $request, string $service)
 * @method static ProxyResponse get(string $url, array|string|null $query = null)
 * @method static ProxyResponse head(string $url, array|string|null $query = null)
 * @method static ProxyResponse patch(string $url, array $data = [])
 * @method static ProxyResponse post(string $url, array $data = [])
 * @method static ProxyResponse put(string $url, array $data = [])
 * @method static ProxyResponse delete(string $url, array $data = [])
 * @method static ProxyResponse send(string $method, string $url, array $options = [])
 * @method static ResponseSequence fakeSequence(string $urlPattern = '*')
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
