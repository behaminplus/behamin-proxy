<?php


namespace Behamin\ServiceProxy\Responses;

use ArrayAccess;
use Behamin\ServiceProxy\Exceptions\ProxyException;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ResponseWrapper implements Jsonable, Responsable, ArrayAccess
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

    public function toResponse($request)
    {
        return response($this->response()->body(), $this->response->status());
    }

    public function jsonResponse(): JsonResponse
    {
        return response($this->response()->json(), $this->response->status())->json();
    }

    public function toJson($options = 0)
    {
        return $this->json();
    }

    public function offsetExists($offset): bool
    {
        return $this->response->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->response->offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->response->offsetSet($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->response->offsetUnset($offset);
    }

    public function collect(): Collection
    {
        return $this->response->collect();
    }
}
