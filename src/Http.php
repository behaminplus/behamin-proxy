<?php


namespace Behamin\ServiceProxy;


use Behamin\ServiceProxy\Request\PendingRequest;
use Behamin\ServiceProxy\Request\RequestInfo;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Request;

/**
 * Class Http
 * @package Behamin\ServiceProxy
 *
 * @method \Illuminate\Http\Client\Response delete(string $url = null, array $data = [])
 * @method \Illuminate\Http\Client\Response get(string $url = null, array|string|null $query = null)
 * @method \Illuminate\Http\Client\Response head(string $url = null, array|string|null $query = null)
 * @method \Illuminate\Http\Client\Response patch(string $url = null, array $data = [])
 * @method \Illuminate\Http\Client\Response post(string $url = null, array $data = [])
 * @method \Illuminate\Http\Client\Response put(string $url = null, array $data = [])
 * @method \Illuminate\Http\Client\Response send(string $method, string $url, array $options = [])
 *
 * @see PendingRequest
 */
class Http extends Factory implements RequestInfo
{
    protected string $service;
    protected array $headers = [];
    protected array $options = [];
    protected $path = null;
    protected $files = null;

    public function service(string $service): Http
    {
        $this->service = $service;
        return $this;
    }

    public function request(Request $request): \Illuminate\Http\Client\Response
    {
        $this->path = $request->path();
        $this->headers = $request->headers->all();
        $this->options = $request->all();

        switch ($request->method()) {
            case Request::METHOD_GET:
                return $this->get();
            case Request::METHOD_POST:
                return $this->post();
            case Request::METHOD_DELETE:
                return $this->delete();
            case Request::METHOD_HEAD:
                return $this->head();
            case Request::METHOD_PATCH:
                return $this->patch();
            case Request::METHOD_PUT:
                return $this->put();
            default:
                throw new \Exception('method is not acceptable');
        }
    }


    /**
     * Create a new pending request instance for this factory.
     *
     * @return PendingRequest
     */
    protected function newPendingRequest(): PendingRequest
    {
        return new PendingRequest($this, $this);
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

    public function getService(): string
    {
        return $this->service;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getFiles()
    {
        // TODO: Implement getFiles() method.
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
