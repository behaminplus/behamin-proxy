<?php


namespace Behamin\ServiceProxy\Response;

use Behamin\ServiceProxy\Exceptions\ProxyException;
use Behamin\ServiceProxy\Request\RequestInfo;
use Illuminate\Http\Client\Response as HttpResponse;

class ResponseWrapper
{

    private HttpResponse $response;
    private RequestInfo $requestInfo;

    public function __construct(HttpResponse $response, RequestInfo $requestInfo)
    {
        $this->response = $response;
        $this->requestInfo = $requestInfo;
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

    public function onError(\Closure $closure): ResponseWrapper
    {
        if ($this->response->failed()) {
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
        if ($this->response->failed()) {
            return new ProxyException($this);
        }
    }

    /**
     * @return RequestInfo
     */
    public function getRequestInfo(): RequestInfo
    {
        return $this->requestInfo;
    }
}
