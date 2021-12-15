<?php

namespace Behamin\ServiceProxy\Exceptions;

use Behamin\ServiceProxy\Responses\ResponseWrapper;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

class ProxyException extends HttpClientException
{
    public ResponseWrapper $responseWrapper;

    public function __construct(ResponseWrapper $responseWrapper)
    {
        $this->responseWrapper = $responseWrapper;
        parent::__construct(
            $this->prepareMessage($responseWrapper->response()),
            $responseWrapper->response()->status()
        );
    }

    protected function prepareMessage(Response $response): string
    {
        $proxyPath = $this->responseWrapper->response()->effectiveUri()->getPath();
        return "Request from {$proxyPath} failed with status code {$response->status()}";
    }

    public function render(Request $request)
    {
        $proxyError = $this->responseWrapper->json();
        if (isset($proxyError['error'])) {
            $errors = $this->responseWrapper->errors();
        } elseif (isset($proxyError['message'])) {
            $errors['message'] = $this->responseWrapper->message();
            $errors['errors'] = null;
        } else {
            $errors['message'] = $this->getMessage();
            $errors['errors'] = $this->responseWrapper->json();
        }
        return apiResponse()->errors($errors['message'], $errors['errors'])->status($this->getCode())->get();
    }

    /**
     * Get the default context variables for logging.
     *
     * @return array
     */
    public function context(): array
    {
        return [];
    }
}
