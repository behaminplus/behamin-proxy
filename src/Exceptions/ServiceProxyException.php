<?php

namespace Behamin\ServiceProxy\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ServiceProxyException extends Exception
{
    protected $message;
    protected $code;
    protected $errors;

    public function __construct(
        $message,
        $code = Response::HTTP_UNPROCESSABLE_ENTITY,
        array $errors = null
    ) {
        parent::__construct($message, $code);
        $this->errors = $errors;
        $this->code = $code;
        $this->message = $message;
    }

    public function render(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => null,
            'message' => null,
            'error' => [
                'message' => $this->message,
                'errors' => $this->errors
            ]
        ], $this->code);
    }
}
