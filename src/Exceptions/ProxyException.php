<?php

namespace Behamin\ServiceProxy\Exceptions;

use Behamin\ServiceProxy\Responses\ProxyResponse;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;

class ProxyException extends HttpClientException
{
    public ProxyResponse $responseWrapper;

    public function __construct(ProxyResponse $responseWrapper)
    {
        $this->responseWrapper = $responseWrapper;

        parent::__construct(
            $this->prepareMessage($responseWrapper->response()),
            $responseWrapper->response()->status()
        );
    }

    protected function prepareMessage(Response $response): string
    {
        $proxyPath = optional($this->responseWrapper->response()->effectiveUri())->getPath();

        return "Request from $proxyPath failed with status code {$response->status()}";
    }

    public function render(): JsonResponse
    {
        $jsonResponse = $this->responseWrapper->json();

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
