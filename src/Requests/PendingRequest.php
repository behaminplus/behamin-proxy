<?php

namespace Behamin\ServiceProxy\Requests;

use Behamin\ServiceProxy\Http;
use Behamin\ServiceProxy\Responses\Mock;
use Behamin\ServiceProxy\Responses\ProxyResponse;
use Behamin\ServiceProxy\UrlGenerator;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest as HttpPendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class PendingRequest extends HttpPendingRequest
{
    private string $service = '';

    public function __construct($factory = null)
    {
        parent::__construct($factory);
    }

    public function request(Request $request, string $service): ProxyResponse
    {
        $this->service = $service;
        $path = $request->path();
        $data = $request->all();

        foreach ($request->allFiles() as $name => $file) {
            unset($data[$name]);
            $this->attach($name, $request->file($name)->getContent(), $request->file($name)->getClientOriginalName());
        }

        switch ($request->method()) {
            case Request::METHOD_GET:
                return $this->get($path, $data);
            case Request::METHOD_POST:
                return $this->post($path, $data);
            case Request::METHOD_DELETE:
                return $this->delete($path, $data);
            case Request::METHOD_HEAD:
                return $this->head($path, $data);
            case Request::METHOD_PATCH:
                return $this->patch($path, $data);
            case Request::METHOD_PUT:
                return $this->put($path, $data);
            default:
                throw new NotAcceptableHttpException();
        }
    }

    public function get(string $url = null, $query = null)
    {
        return $this->respond($url, $query, Request::METHOD_GET);
    }

    public function delete($url = null, $data = [])
    {
        return $this->respond($url, $data, Request::METHOD_DELETE);
    }

    public function head(string $url = null, $query = null)
    {
        return $this->respond($url, $query, Request::METHOD_HEAD);
    }

    public function patch($url, $data = [])
    {
        return $this->respond($url, $data, Request::METHOD_PATCH);
    }

    public function post(string $url, array $data = [])
    {
        return $this->respond($url, $data, Request::METHOD_POST);
    }

    public function put($url, $data = [])
    {
        return $this->respond($url, $data, Request::METHOD_PUT);
    }

    public function prepare(): void
    {
        $this->withHeaders(config('proxy.global_headers', []));
    }

    protected function makePromise(string $method, string $url, array $options = []): PromiseInterface
    {
        return $this->promise = $this->sendRequest($method, $url, $options)
            ->then(function (MessageInterface $message) {
                return new ProxyResponse(tap(new Response($message), function ($response) {
                    $this->populateResponse($response);
                    $this->dispatchResponseReceivedEvent($response);
                }));
            })
            ->otherwise(function (TransferException $e) {
                return $e instanceof RequestException ? $this->populateResponse(new Response($e->getResponse())) : $e;
            });
    }

    /**
     * @param  null|string  $path
     * @return string
     */
    private function fullUrl(?string $path): string
    {
        $baseUrl = UrlGenerator::baseUrl();
        $servicePath = $this->service;

        if (Str::endsWith($baseUrl, '/')) {
            $baseUrl = Str::substr($baseUrl, 0, -1);
        }

        if (Str::startsWith($servicePath, '/')) {
            $servicePath = Str::substr($baseUrl, 1);
        }

        if (Str::startsWith($path, '/')) {
            $finalPath = Str::substr($path, 1);
        } else {
            $finalPath = $path;
        }

        return $baseUrl.($servicePath === '' ? $servicePath : '/'.$servicePath).'/'.$finalPath;
    }

    private function respond($url, $data, $method)
    {
        if ($this->factory instanceof Http && $this->factory->getMockPath() && app()->runningUnitTests()) {
            $result = Mock::fakeResponse($this->factory->getMockPath());
        } else {
            $this->prepare();
            $method = Str::lower($method);
            $result = parent::$method($this->fullUrl($url), $data);
        }

        if ($result instanceof PromiseInterface) {
            return $result;
        }

        return new ProxyResponse($result);
    }
}
