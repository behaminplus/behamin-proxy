<?php

namespace Behamin\ServiceProxy;

use Behamin\ServiceProxy\Requests\PendingRequest;
use Behamin\ServiceProxy\Responses\Mock;
use Behamin\ServiceProxy\Responses\ProxyResponse;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http as HttpFactory;
use Illuminate\Support\Str;
use ReflectionObject;

/**
 * Class Http
 * @package Behamin\ServiceProxy
 *
 * @method PendingRequest accept(string $contentType)
 * @method PendingRequest acceptJson()
 * @method PendingRequest asForm()
 * @method PendingRequest asJson()
 * @method PendingRequest asMultipart()
 * @method PendingRequest async()
 * @method PendingRequest attach(string|array $name, string $contents = '', string|null $filename = null, array $headers = [])
 * @method PendingRequest baseUrl(string $url)
 * @method PendingRequest beforeSending(callable $callback)
 * @method PendingRequest bodyFormat(string $format)
 * @method PendingRequest contentType(string $contentType)
 * @method PendingRequest dd()
 * @method PendingRequest dump()
 * @method PendingRequest retry(int $times, int $sleep = 0)
 * @method PendingRequest sink(string|resource $to)
 * @method PendingRequest stub(callable $callback)
 * @method PendingRequest timeout(int $seconds)
 * @method PendingRequest withBasicAuth(string $username, string $password)
 * @method PendingRequest withBody(resource|string $content, string $contentType)
 * @method PendingRequest withCookies(array $cookies, string $domain)
 * @method PendingRequest withDigestAuth(string $username, string $password)
 * @method PendingRequest withHeaders(array $headers)
 * @method PendingRequest withMiddleware(callable $middleware)
 * @method PendingRequest withOptions(array $options)
 * @method PendingRequest withToken(string $token, string $type = 'Bearer')
 * @method PendingRequest withUserAgent(string $userAgent)
 * @method PendingRequest domain(string $domain)
 * @method PendingRequest withoutRedirecting()
 * @method PendingRequest withoutVerifying()
 * @method ProxyResponse delete(string $url = null, array $data = [])
 * @method ProxyResponse get(string $url = null, array|string|null $query = null)
 * @method ProxyResponse head(string $url = null, array|string|null $query = null)
 * @method ProxyResponse patch(string $url = null, array $data = [])
 * @method ProxyResponse post(string $url = null, array $data = [])
 * @method ProxyResponse put(string $url = null, array $data = [])
 * @method ProxyResponse send(string $method, string $url, array $options = [])
 * @method ProxyResponse request(Request $request, string $service)
 *
 * @see PendingRequest
 */
class Http extends Factory
{
    protected array $files = [];
    private ?string $mockPath = null;
    private array $fakes = [];

    /**
     * Create a new pending request instance for this factory.
     *
     * @return PendingRequest
     */
    protected function newPendingRequest(): PendingRequest
    {
        return new PendingRequest($this);
    }

    public function mock($jsonPath): Http
    {
        if (!is_array($jsonPath)) {
            $this->mockPath = $jsonPath;
            return $this;
        }

        $fakeItem = $this->prepareFakeItem($jsonPath);
        $this->fakes[key($fakeItem)] = Arr::first($fakeItem);
        HttpFactory::fake($fakeItem);
        $this->mockPath = null;

        return $this;
    }

    public function clearExistingFakes(): self
    {
        $reflection = new ReflectionObject(HttpFactory::getFacadeRoot());
        $property = $reflection->getProperty('stubCallbacks');
        $property->setAccessible(true);
        $property->setValue(HttpFactory::getFacadeRoot(), collect());

        return $this;
    }

    public function trimUrl($url): string
    {
        return trim($url, '/');
    }

    private function prepareFakeItem($fakeArray)
    {
        $url = key($fakeArray);
        $jsonPath = $fakeArray[$url];

        return [$this->trimUrl($url) => $this->getJsonContent($jsonPath)];
    }

    private function getJsonContent($jsonPath)
    {
        $mockDirectory = base_path().DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'mock'.DIRECTORY_SEPARATOR;
        $jsonFile = file_get_contents($mockDirectory.$jsonPath);
        return json_decode($jsonFile, true, 512, JSON_THROW_ON_ERROR);
    }

    public function hasFakes()
    {
        return !empty($this->fakes);
    }

    public function hasFake($url)
    {
        return !empty($this->fakes[$this->trimUrl($url)]);
    }

    /**
     * @return string|null
     */
    public function getMockPath(): ?string
    {
        return $this->mockPath;
    }

    /**
     * Execute a method against a new pending request instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     * @throws \JsonException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if ($this->isSetMocking() && $this->isHttpRequestMethod($method)) {
            $this->mock([Arr::first($parameters) => $this->mockPath]);
        }

        return tap($this->newPendingRequest(), function ($request) {
            $request->stub($this->stubCallbacks);
        })->{$method}(...$parameters);
    }

    private function isSetMocking(): bool
    {
        return !empty($this->mockPath);
    }

    private function isHttpRequestMethod($method): bool
    {
        return in_array(Str::lower($method), ['post', 'get', 'head', 'delete', 'put', 'patch']);
    }
}
