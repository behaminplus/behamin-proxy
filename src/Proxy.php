<?php

namespace BSProxy;

use BSProxy\Exceptions\ServiceProxyException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Class Proxy
 *
 * @package BSProxy
 */
class Proxy
{
    /**
     * @var Request $request
     */
    private $request = null;
    private $method = 'get';
    private $headers = ["Accept" => "application/json"];
    private $path = null;
    private $data = null;
    private $token = null;
    private $modelId = null;
    private $serviceUrl = null;

    public function __construct()
    {
        $this->path = '/';
    }

    /**
     * @return null
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * @param  null  $modelId
     *
     * @return Proxy
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
        return $this;
    }


    /**
     * @param $service
     * @param  string  $app
     *
     * @return string
     * @throws ServiceProxyException
     */
    public function setServiceUrl($service, $app = 'GLOBAL_APP_URL'): string
    {
        $parsedUrl = parse_url(config('proxy-service-url.'.$app));
        $path = config('proxy-service-url.'.$service, null);
        $scheme = ($parsedUrl['scheme'] ?? 'https').'://';
        $host = $parsedUrl['host'];

        if ($path === null) {
            throw new ServiceProxyException(
                $service.' service not found url address.'
            );
        }

        $this->serviceUrl = $scheme.rtrim($host, '/').'/'.ltrim($path, '/');
        return $this;
    }

    public function getServiceUrl()
    {
        return $this->serviceUrl;
    }


    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param  string  $method
     *
     * @return Proxy
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param  null  $request
     *
     * @return Proxy
     */
    public function setRequest($request)
    {
        $this->request = $request;
        $this->fetchFromRequest($request);
        return $this;
    }

    /**
     * @param $request
     */
    protected function fetchFromRequest($request)
    {
        if ($request !== null) {
            $this->method = $request->method();
            $this->path = $request->path();
            $this->data = $request->all();
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     *
     * @return Proxy
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param  string[]  $headers
     *
     * @return Proxy
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param $header
     *
     * @return $this
     */
    public function addHeader($header)
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * @param $headers
     *
     * @return $this
     */
    public function addHeaders($headers)
    {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param  mixed  $data
     *
     * @return Proxy
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param  Request  $request
     * @param $service
     * @param $method
     * @param $path
     * @param  null  $modelId
     * @param  array  $data
     * @param  array  $headers
     *
     * @return mixed
     * @throws ServiceProxyException
     */
    public function makeRequest(
        $request,
        $service,
        $method = 'get',
        $path = null,
        $modelId = null,
        $data = [],
        $headers = []
    ) {
        if ($request !== null) {
            $this->setRequest($request);
        }

        $this->setServiceUrl($service);

        if (!empty($headers)) {
            $this->addHeaders($headers);
        }

        $this->setToken();

        if ($path !== null) {
            $this->setPath($path);
        }

        if ($modelId !== null) {
            $this->setModelId($modelId);
        }

        if ($method !== null) {
            $this->setMethod($method);
        }

        $response = Http::withHeaders($headers);

        if ($this->hasToken()) {
            $response = $response->withToken($this->getToken());
        }

        if ($request && $request->hasFile('media')) {
            $response = $response->attach(
                'media',
                $request->file('media')->get(),
                $request->file('media')->getClientOriginalName()
            );
        }

        $response = $response->{$this->method}(
            $this->getServiceUrl().$this->getPath().'/'.$this->getModelId(),
            $data
        );
        $jsonResponse = $response->json();

        $this->handleRequestErrors($response, $jsonResponse);

        return $jsonResponse;
    }

    /**
     * @return bool
     */
    protected function hasToken(): bool
    {
        return ($this->getToken() !== null);
    }

    /**
     * @param  null  $token
     *
     * @return mixed|string|null
     */
    public function setToken($token = null)
    {
        if ($token !== null) {
            $this->token = $token;
        } else {
            $token = $this->headers['token'] ?? null;
            if ($this->request !== null) {
                $token = $this->request->bearerToken();
            }
            $this->token = $token;
        }
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $response
     * @param $jsonResponse
     *
     * @throws ServiceProxyException
     */
    protected function handleRequestErrors($response, $jsonResponse): void
    {
        if ($this->isBadRequest($response)) {
            $errorMessage = $errors = null;
            if ($this->responseHaseError($jsonResponse)) {
                $errorMessage = $this->getResponseMessage($jsonResponse);
                $errors = $this->getResponseError($jsonResponse);
            }

            throw new ServiceProxyException(
                $errorMessage ?: 'request not any response in body.',
                $response->status(),
                null,
                $errors
            );
        }
    }

    private function isBadRequest($response): bool
    {
        //TODO check Conflict with laravel error code 500
        return $response->status() >= 400 && $response->status() < 500;
    }

    private function responseHaseError($jsonResponse): bool
    {
        return is_array($jsonResponse)
            && array_key_exists(
                'error',
                $jsonResponse
            );
    }

    private function getResponseMessage($jsonResponse)
    {
        if (array_key_exists('message', $jsonResponse['error'])) {
            $errorMessage = $jsonResponse['error']['message'];
        } else {
            $errorMessage = $jsonResponse['error'];
        }
        return $errorMessage;
    }

    /**
     * @param $jsonResponse
     *
     * @return mixed
     */
    private function getResponseError($jsonResponse)
    {
        if (array_key_exists('errors', $jsonResponse['error'])) {
            return $jsonResponse['error']['errors'];
        }
        return null;
    }
}
