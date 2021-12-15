<?php

namespace Behamin\ServiceProxy\Exceptions;

use Behamin\ServiceProxy\Responses\ProxyResponse;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;

class ProxyException extends HttpClientException
{
    public ProxyResponse $proxyResponse;

    public function __construct(ProxyResponse $proxyResponse)
    {
        $this->proxyResponse = $proxyResponse;

        parent::__construct(
            $this->prepareMessage($proxyResponse->response()),
            $proxyResponse->response()->status()
        );
    }

    protected function prepareMessage(Response $response): string
    {
        $proxyPath = optional($this->proxyResponse->response()->effectiveUri())->getPath();

        return "Request from $proxyPath failed with status code {$response->status()}";
    }

    public function render(): JsonResponse
    {
        $jsonResponse = $this->proxyResponse->json();

        if (isset($jsonResponse['error']['message'], $jsonResponse['error']['errors'])) {
            $error = $jsonResponse['error'];
        } elseif (isset($jsonResponse['message'], $jsonResponse['trace'])) {
            $error['message'] = $this->getMessage();
            $error['errors'] = $jsonResponse;
        } else {
            $error['message'] = $jsonResponse['message'] ?? $this->getMessage();
            $error['errors'] = null;
        }

        return apiResponse()->errors($error['message'], $error['errors'])->status($this->getCode())->get();
    }
}
