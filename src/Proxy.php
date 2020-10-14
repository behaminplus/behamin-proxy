<?php

namespace BSProxy;

use BSProxy\Enums\Service;
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
    private $method = 'get';
    private $request = null;
    private $path, $headers, $data;
    private $serviceProxy;

    public function __construct()
    {
        $this->path = '/';
        $this->headers = ["Accept" => "application/json"];
    }

    /**
     * @param $service
     * @param  string  $app
     *
     * @return string
     * @throws ServiceProxyException
     */
    public function getServiceUrl($service, $app = 'GLOBAL_APP_URL'): string
    {
        $host = parse_url(config('proxy-service-url.' . $app), PHP_URL_HOST);
        $path = config('proxy-service-url.' . $service);
        if ($path === null){
            throw new ServiceProxyException($service. ' service path not found.');
        }

        return  'https://' . rtrim($host, '/') . '/' . ltrim($path, '/');
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
     */
    public function setMethod(string $method): Proxy
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
     */
    public function setRequest($request): Proxy
    {
        $this->request = $request;
        return $this;
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
     */
    public function setPath(string $path): Proxy
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
     */
    public function setHeaders(array $headers): Proxy
    {
        $this->headers = $headers;
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
     */
    public function setData($data): Proxy
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
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
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
        [$token, $method, $path, $data, $headers, $serviceUrl]
            = $this->getRequestFields(
            $request,
            $service,
            $method,
            $path,
            $modelId,
            $data,
            $headers
        );
        $response = Http::withToken($token)->withHeaders($headers);
        if ($request && $request->hasFile('media')) {
            $response = $response->attach(
                'media',
                $request->file('media')->get(),
                $request->file('media')->getClientOriginalName()
            );
        }

        $response = $response->$method(
            $serviceUrl.$path,
            $data
        );
        $jsonResponse = $response->json();
        $this->handleRequestErrors($response, $jsonResponse);
        return $jsonResponse;
    }

    /**
     * @param  Request  $request
     * @param $service
     * @param $method
     * @param $path
     * @param $modelId
     * @param $data
     * @param $headers
     *
     * @return array
     */
    protected function getRequestFields(
        $request,
        $service,
        $method,
        $path,
        $modelId,
        $data,
        $headers
    ): array {
        $token = $headers['token'] ?? null;
        if ($request !== null) {
            $method = $method ?? $request->method();
            $path = $path ?? $request->path();
            $token = $request->bearerToken();
            $data = $data ?? $request->all();
        }

        $headers = array_merge($this->headers, $headers);

        $serviceUrl = $this->getServiceUrl($service);
        //if it's not' found ?
        $path = $path ?? $this->path;
        $path = ($modelId === null) ? $path : ($path.'/'.$modelId);
        return array($token, $method, $path, $data, $headers, $serviceUrl);
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
        return is_array($jsonResponse) && array_key_exists('error', $jsonResponse);
    }

    private function getResponseMessage($jsonResponse){
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
    private function getResponseError($jsonResponse){
        if (array_key_exists('errors', $jsonResponse['error'])){
            return $jsonResponse['error']['errors'];
        }
        return null;
    }
}
