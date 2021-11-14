<?php

namespace Behamin\ServiceProxy\Exceptions;

use Behamin\ServiceProxy\Responses\ResponseWrapper;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response;

class ProxyException extends HttpClientException
{
    public ResponseWrapper $responseWrapper;

    public function __construct(ResponseWrapper $responseWrapper)
    {
        parent::__construct($this->prepareMessage($responseWrapper->response()), $responseWrapper->response()->status());

        $this->responseWrapper = $responseWrapper;
    }

    protected function prepareMessage(Response $response): string
    {
        $message = "HTTP request returned status code {$response->status()}";

        $summary = class_exists(\GuzzleHttp\Psr7\Message::class)
            ? \GuzzleHttp\Psr7\Message::bodySummary($response->toPsrResponse())
            : \GuzzleHttp\Psr7\get_message_body_summary($response->toPsrResponse());

        return is_null($summary) ? $message : $message.":\n$summary\n";
    }

    /**
     * Get the default context variables for logging.
     *
     * @return array
     */
    protected function context(): array
    {
        return [];
    }
}
