<?php

namespace Behamin\ServiceProxy\Exceptions;

use Exception;
use Illuminate\Http\Request;

class ServiceProxyException extends Exception
{
    protected $message;
    protected $code;
    protected $errors;
    protected $url;
    protected $service;

    public function __construct(
        $message,
        $service,
        $url = null,
        $code = 400,
        array $errors = null
    ) {
        parent::__construct($message, $code);
        $this->errors = $errors;
        $this->message = $message;
        $this->url = $url;
        $this->service = $service;
        $this->code = $code;
    }

    public function context()
    {
        return [
            'message. ' => $this->message,
            'request from. ' => $this->url,
            'service name. ' => $this->service,
            'code. ' => $this->code,
        ];

    }

    public function render(Request $request)
    {
        return response()->json([
            'data' => null,
            'message' => null,
            'error' => [
                'message' => $this->message,
                'errors' => $this->errors,
            ],
        ], $this->code);
    }
}
