<?php


namespace Behamin\ServiceProxy;


use Behamin\ServiceProxy\Request\PendingRequest;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;

/**
 * Class Http
 * @package Behamin\ServiceProxy
 *
 * @method \Behamin\ServiceProxy\Request\PendingRequest accept(string $contentType)
 * @method \Behamin\ServiceProxy\Request\PendingRequest acceptJson()
 * @method \Behamin\ServiceProxy\Request\PendingRequest asForm()
 * @method \Behamin\ServiceProxy\Request\PendingRequest asJson()
 * @method \Behamin\ServiceProxy\Request\PendingRequest asMultipart()
 * @method \Behamin\ServiceProxy\Request\PendingRequest async()
 * @method \Behamin\ServiceProxy\Request\PendingRequest attach(string|array $name, string $contents = '', string|null $filename = null, array $headers = [])
 * @method \Behamin\ServiceProxy\Request\PendingRequest baseUrl(string $url)
 * @method \Behamin\ServiceProxy\Request\PendingRequest beforeSending(callable $callback)
 * @method \Behamin\ServiceProxy\Request\PendingRequest bodyFormat(string $format)
 * @method \Behamin\ServiceProxy\Request\PendingRequest contentType(string $contentType)
 * @method \Behamin\ServiceProxy\Request\PendingRequest dd()
 * @method \Behamin\ServiceProxy\Request\PendingRequest dump()
 * @method \Behamin\ServiceProxy\Request\PendingRequest retry(int $times, int $sleep = 0)
 * @method \Behamin\ServiceProxy\Request\PendingRequest sink(string|resource $to)
 * @method \Behamin\ServiceProxy\Request\PendingRequest stub(callable $callback)
 * @method \Behamin\ServiceProxy\Request\PendingRequest timeout(int $seconds)
 * @method \Behamin\ServiceProxy\Request\PendingRequest withBasicAuth(string $username, string $password)
 * @method \Behamin\ServiceProxy\Request\PendingRequest withBody(resource|string $content, string $contentType)
 * @method \Behamin\ServiceProxy\Request\PendingRequest withCookies(array $cookies, string $domain)
 * @method \Behamin\ServiceProxy\Request\PendingRequest withDigestAuth(string $username, string $password)
 * @method \Behamin\ServiceProxy\Request\PendingRequest withHeaders(array $headers)
 * @method \Behamin\ServiceProxy\Request\PendingRequest withMiddleware(callable $middleware)
 * @method \Behamin\ServiceProxy\Request\PendingRequest withOptions(array $options)
 * @method \Behamin\ServiceProxy\Request\PendingRequest withToken(string $token, string $type = 'Bearer')
 * @method \Behamin\ServiceProxy\Request\PendingRequest withUserAgent(string $userAgent)
 * @method \Behamin\ServiceProxy\Request\PendingRequest withoutRedirecting()
 * @method \Behamin\ServiceProxy\Request\PendingRequest withoutVerifying()
 * @method \Behamin\ServiceProxy\Response\ResponseWrapper delete(string $url = null, array $data = [])
 * @method \Behamin\ServiceProxy\Response\ResponseWrapper get(string $url = null, array|string|null $query = null)
 * @method \Behamin\ServiceProxy\Response\ResponseWrapper head(string $url = null, array|string|null $query = null)
 * @method \Behamin\ServiceProxy\Response\ResponseWrapper patch(string $url = null, array $data = [])
 * @method \Behamin\ServiceProxy\Response\ResponseWrapper post(string $url = null, array $data = [])
 * @method \Behamin\ServiceProxy\Response\ResponseWrapper put(string $url = null, array $data = [])
 * @method \Behamin\ServiceProxy\Response\ResponseWrapper send(string $method, string $url, array $options = [])
 * @method \Behamin\ServiceProxy\Response\ResponseWrapper request(Request $request, string $service)
 *
 * @see PendingRequest
 */
class Http extends Factory
{
    protected array $files = [];

    /**
     * Create a new pending request instance for this factory.
     *
     * @return PendingRequest
     */
    protected function newPendingRequest(): PendingRequest
    {
        return new PendingRequest($this);
    }

    /**
     * Execute a method against a new pending request instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return tap($this->newPendingRequest(), function ($request) {
            $request->stub($this->stubCallbacks);
        })->{$method}(...$parameters);
    }
}
