<?php


namespace Behamin\ServiceProxy;


use Behamin\ServiceProxy\Requests\PendingRequest;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;

/**
 * Class Http
 * @package Behamin\ServiceProxy
 *
 * @method \Behamin\ServiceProxy\Requests\PendingRequest accept(string $contentType)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest acceptJson()
 * @method \Behamin\ServiceProxy\Requests\PendingRequest asForm()
 * @method \Behamin\ServiceProxy\Requests\PendingRequest asJson()
 * @method \Behamin\ServiceProxy\Requests\PendingRequest asMultipart()
 * @method \Behamin\ServiceProxy\Requests\PendingRequest async()
 * @method \Behamin\ServiceProxy\Requests\PendingRequest attach(string|array $name, string $contents = '', string|null $filename = null, array $headers = [])
 * @method \Behamin\ServiceProxy\Requests\PendingRequest baseUrl(string $url)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest beforeSending(callable $callback)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest bodyFormat(string $format)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest contentType(string $contentType)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest dd()
 * @method \Behamin\ServiceProxy\Requests\PendingRequest dump()
 * @method \Behamin\ServiceProxy\Requests\PendingRequest retry(int $times, int $sleep = 0)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest sink(string|resource $to)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest stub(callable $callback)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest timeout(int $seconds)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withBasicAuth(string $username, string $password)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withBody(resource|string $content, string $contentType)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withCookies(array $cookies, string $domain)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withDigestAuth(string $username, string $password)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withHeaders(array $headers)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withMiddleware(callable $middleware)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withOptions(array $options)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withToken(string $token, string $type = 'Bearer')
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withUserAgent(string $userAgent)
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withoutRedirecting()
 * @method \Behamin\ServiceProxy\Requests\PendingRequest withoutVerifying()
 * @method \Behamin\ServiceProxy\Responses\ResponseWrapper delete(string $url = null, array $data = [])
 * @method \Behamin\ServiceProxy\Responses\ResponseWrapper get(string $url = null, array|string|null $query = null)
 * @method \Behamin\ServiceProxy\Responses\ResponseWrapper head(string $url = null, array|string|null $query = null)
 * @method \Behamin\ServiceProxy\Responses\ResponseWrapper patch(string $url = null, array $data = [])
 * @method \Behamin\ServiceProxy\Responses\ResponseWrapper post(string $url = null, array $data = [])
 * @method \Behamin\ServiceProxy\Responses\ResponseWrapper put(string $url = null, array $data = [])
 * @method \Behamin\ServiceProxy\Responses\ResponseWrapper send(string $method, string $url, array $options = [])
 * @method \Behamin\ServiceProxy\Responses\ResponseWrapper request(Request $request, string $service)
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
