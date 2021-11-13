<?php


namespace Behamin\ServiceProxy\Response;

use Behamin\ServiceProxy\Exceptions\ProxyException;
use Illuminate\Http\Client\Response as HttpResponse;

class ResponseWrapper
{

    private HttpResponse $response;

    public function __construct(HttpResponse $response)
    {
        $this->response = $response;
    }

    public function data()
    {
        return $this->response->json()['data'];
    }

    public function message()
    {
        return $this->response->json()['message'];
    }

    public function errors()
    {
        return $this->response->json()['errors'];
    }

    public function items()
    {
        return $this->response->json()['data']['items'];
    }

    public function count()
    {
        return $this->response->json()['data']['count'];
    }

    public function response(): HttpResponse
    {
        return $this->response;
    }

    public function onSuccess(\Closure $closure): ResponseWrapper
    {
        if ($this->response->successful()) {
            $closure($this);
        }
        return $this;
    }

    public function onDataSuccess(\Closure $closure): ResponseWrapper
    {
        if ($this->response->successful()) {
            $closure($this->data());
        }
        return $this;
    }

    public function onCollectionSuccess(\Closure $closure): ResponseWrapper
    {
        if ($this->response->successful()) {
            $closure($this->items(), $this->count());
        }
        return $this;
    }

    public function onError(\Closure $closure): ResponseWrapper
    {
        if ($this->response->failed()) {
            $closure($this->toException());
        }
        return $this;
    }

    public function json()
    {
        return $this->response->json();
    }

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return ProxyException|void
     */
    public function toException(): ProxyException
    {
        if ($this->response->failed()) {
            return new ProxyException($this);
        }
    }

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     */
    public function throw(): ResponseWrapper
    {
        $callback = func_get_args()[0] ?? null;

        if ($this->response->failed()) {
            throw tap($this->toException(), function ($exception) use ($callback) {
                if ($callback && is_callable($callback)) {
                    $callback($this, $exception);
                }
            });
        }

        return $this;
    }
}
