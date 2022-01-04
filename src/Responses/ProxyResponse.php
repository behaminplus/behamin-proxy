<?php

namespace Behamin\ServiceProxy\Responses;

use ArrayAccess;
use Behamin\ServiceProxy\Exceptions\ProxyException;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProxyResponse implements Jsonable, Responsable, ArrayAccess, Arrayable
{
    private HttpResponse $response;

    public function __construct(HttpResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Get data from response or a subset of it.
     *
     * @param  array|string|null  $keys
     * @param  mixed  $default
     * @return array|mixed
     */
    public function data($keys = null, $default = null)
    {
        $data = $this->json()['data'];

        if (is_null($keys)) {
            return $data;
        }

        if (is_string($keys)) {
            return $data[$keys] ?? $default;
        }

        return Arr::only($data, $keys);
    }

    /**
     * Get response message.
     *
     * @return string|null
     */
    public function message(): ?string
    {
        return $this->json()['message'];
    }

    public function error()
    {
        return $this->json()['error'];
    }

    public function items()
    {
        return $this->json()['data']['items'];
    }

    public function count()
    {
        return $this->json()['data']['count'];
    }

    /**
     * Get response data as a collection.
     *
     * @return Collection
     */
    public function collect(): Collection
    {
        if (isset($this->json()['data']['items'])) {
            return Collection::make($this->items());
        }

        return Collection::make($this->data());
    }

    public function json()
    {
        return $this->response()->json();
    }

    /**
     * Get response status code.
     *
     * @return int
     */
    public function status(): int
    {
        return $this->response()->status();
    }

    public function jsonResponse(): JsonResponse
    {
        return response()->json($this->response()->json(), $this->status());
    }

    public function toJson($options = 0)
    {
        return json_encode($this->json(), $options | JSON_THROW_ON_ERROR);
    }

    public function toArray()
    {
        return $this->json();
    }

    public function onSuccess(Closure $closure): ProxyResponse
    {
        if ($this->response()->successful()) {
            $closure($this);
        }

        return $this;
    }

    public function onDataSuccess(Closure $closure): ProxyResponse
    {
        if ($this->response()->successful()) {
            $closure($this->data());
        }

        return $this;
    }

    public function onCollectionSuccess(Closure $closure): ProxyResponse
    {
        if ($this->response()->successful()) {
            $closure($this->items(), $this->count());
        }

        return $this;
    }

    public function onError(Closure $closure): ProxyResponse
    {
        if ($this->response()->failed()) {
            $closure($this->toException());
        }

        return $this;
    }

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return ProxyException|void
     */
    public function toException(): ProxyException
    {
        if ($this->response()->failed()) {
            return new ProxyException($this);
        }
    }

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     */
    public function throw(?Closure $closure = null): ProxyResponse
    {
        if ($this->response()->failed()) {
            throw tap($this->toException(), function ($exception) use ($closure) {
                if (!is_null($closure)) {
                    $closure($this, $exception);
                }
            });
        }

        return $this;
    }

    public function toResponse($request)
    {
        if ($this->response()->header('Content-Type') === 'application/json') {
            return $this->jsonResponse();
        }

        return response($this->response()->body(), $this->status());
    }

    public function offsetExists($offset): bool
    {
        return $this->response()->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->response()->offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->response()->offsetSet($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->response()->offsetUnset($offset);
    }

    public function response(): HttpResponse
    {
        return $this->response;
    }
}
