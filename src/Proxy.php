<?php

namespace BSProxy;

use BSProxy\Exceptions\ServiceProxyException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

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
    /**
     * @var
     */
    private $method;
    private $headers = ["Accept" => "application/json"];
    private $path = null;
    private $data = [];
    private $token = null;
    private $modelId = null;
    private $serviceUrl = null;
    private $service = null;
    private $files = null;
    private $dispatch = false;
    private $withProxyResponse = false;

    public function __construct()
    {
        $this->path = '/';
    }

    /**
     * @param Request $request
     * @param $service
     * @param $method
     * @param $path
     * @param null $modelId
     * @param array $data
     * @param array $headers
     *
     * @return mixed|BSProxyResponse
     * @throws ServiceProxyException|FileNotFoundException
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

        $this->setService($service);

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

        if ($method !== null and empty($this->getMethod())) {
            $this->setMethod($method);
        }

        if (!empty($data)) {
            $this->setData($data);
        }

        $headers = $this->getHeaders();
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

        $thisProxy = clone $this;

        if ($this->isDispatchRequest()) {
            $response = $this->dispatchRequest();
            return $this->getProxyResponse($response, $thisProxy);
        } else {
            $response = $response->{$this->getMethod()}($this->getServiceRequestUrl(), $this->getData());
        }
        if ($this->isWithProxyResponse()) {
            return $this->getProxyResponse($response, $thisProxy);
        } else {
            $jsonResponse = $response->json();
            $this->handleRequestErrors($response, $jsonResponse);
            $this->resetData();
            return $jsonResponse;
        }
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
            if ($this->responseHasError($jsonResponse)) {
                $errorMessage = $this->getResponseMessage($jsonResponse);
                $errors = $this->getResponseError($jsonResponse);
            }
            //// TODO: Add option "Break" or "Halt"? <-> Exception Stops app
            //
            throw new ServiceProxyException(
                $errorMessage,
                $response->status(),
                $errors
            );
        }
    }

    /**
     * @param $jsonResponse
     * @return bool
     */
    private function responseHasError($jsonResponse): bool
    {
        return is_array($jsonResponse) and (array_key_exists('error', $jsonResponse) or
            array_key_exists('message', $jsonResponse));
    }

    /**
     * @param $jsonResponse
     * @return mixed
     */
    private function getResponseMessage($jsonResponse)
    {
        if (array_key_exists('error', $jsonResponse) and array_key_exists('message', $jsonResponse['error'])) {
            return $jsonResponse['error']['message'];
        } elseif (array_key_exists('message', $jsonResponse)) {
            return $jsonResponse['message'];
        } else {
            return null;
        }
    }

    /**
     * @param $jsonResponse
     *
     * @return mixed
     */
    private function getResponseError($jsonResponse)
    {
        if (array_key_exists('error', $jsonResponse) and array_key_exists('errors', $jsonResponse['error'])) {
            return $jsonResponse['error']['errors'];
        } elseif (array_key_exists('trace', $jsonResponse)) {
            return $jsonResponse['trace'];
        } else {
            return null;
        }
    }

    /**
     * @param $name
     * @param $file
     * @return $this
     */
    public function addFile($name, $file)
    {
        if (!is_array($file) or !$file instanceof UploadedFile) {
            throw new InvalidArgumentException('An uploaded file must be an array or an instance of UploadedFile.');
        }

        if ($file instanceof UploadedFile) {
            $this->files[$name] = $file;
            return $this;
        }

        $fileData = Arr::only($file, ['tmp_name', 'name', 'type', 'error']);
        if (count($fileData) <> 4) {
            throw new InvalidArgumentException(
                'An uploaded file must be an array with tmp_name, name, type, error data  or an instance of UploadedFile.'
            );
        }
        $this->files[$name] = $fileData;
        return $this;
    }


    /**
     * @param $response
     * @param $thisProxy
     * @return BSProxyResponse
     */
    private function getProxyResponse($response, $thisProxy)
    {
        $this->resetData();
        return new BSProxyResponse($response, $thisProxy);
    }

    /**
     * @return $this
     */
    public function withProxyResponse()
    {
        $this->withProxyResponse = true;
        return $this;
    }

    protected function isWithProxyResponse(): bool
    {
        return $this->withProxyResponse;
    }

    /**
     * @param $service
     * @return mixed|BSProxyResponse
     * @throws ServiceProxyException
     */
    public function dispatch($service)
    {
        return $this->setDispatch()->makeRequest(null, $service);
    }

    /**
     * @return mixed
     */
    protected function dispatchRequest()
    {
        $request = Request::create($this->getServiceRequestUrl(), $this->getMethod(), $this->getData());
        if ($this->files) {
            $request->files = new FileBag($this->files);
        }
        return app()->handle($request);
    }

    /**
     * @param $response
     * @return bool
     */
    private function isBadRequest($response): bool
    {
        return $response->status() >= 400 && $response->status() <= 503;
    }

    protected function isDispatchRequest(): bool
    {
        return $this->dispatch;
    }

    /**
     * @return $this
     */
    public function setDispatch()
    {
        $this->dispatch = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceRequestUrl()
    {
        $serviceUrl = trim($this->getServiceUrl(), '/') . '/';

        if ($path = trim($this->getPath(), '/')) {
            if (!Str::contains($path, '?')) {
                $path .= '/';
            }
        }

        if ($modelId = trim($modelId = $this->getModelId(), '/')) {
            $modelId .= '/';
        }

        return trim($serviceUrl . $modelId . $path, '/');
    }

    /**
     *
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param string|array $service
     * @param string $app
     * @return Proxy
     * @throws ServiceProxyException
     */
    public function setService($service, $app = 'GLOBAL_APP_URL')
    {
        if (is_array($service)) {
            [$service, $app] = array_values($service);
        }
        $this->service = $service;
        return $this->setServiceUrl($service, $app);
    }

    /**
     *
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * @param  $modelId
     *
     * @return Proxy
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
        return $this;
    }

    /**
     *
     */
    public function getServiceUrl()
    {
        return $this->serviceUrl;
    }

    /**
     * @param $service
     * @param string $baseUrl
     * @return self
     * @throws ServiceProxyException
     */
    public function setServiceUrl($service, $baseUrl = 'GLOBAL_APP_URL'): self
    {
        $parsedUrl = parse_url(config('proxy-services-url.' . $baseUrl));
        if (empty($parsedUrl['host'])) {
            throw new ServiceProxyException('host address not found in the config file.');
        }

        $scheme = ($parsedUrl['scheme'] ?? 'https') . '://';
        $host = $parsedUrl['host'];

        $port = '';
        if (!empty($parsedUrl['port'])) {
            $port = ':' . $parsedUrl['port'];
        }

        $path = $parsedUrl['path'] ?? '';
        $path .= config('proxy-services-url.' . $service, null);
        if (empty($path)) {
            throw new ServiceProxyException(
                $service . ' service url address not found.'
            );
        }

        $this->serviceUrl = ($scheme . $host . $port) . '/' . trim($path, '/') . '/';
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return Proxy
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param null $request
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
            $this->setMethod($request->method());
            $this->setPath($request->path());
            $this->setData($request->all());
        }
    }

    /**
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string $path
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
     * @param string[] $headers
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
    public function addHeader(array $header)
    {
        $this->headers = array_merge($this->headers, $header);
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
     * @param mixed $data
     *
     * @return Proxy
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }


    /**
     * @return bool
     */
    protected function hasToken(): bool
    {
        return ($this->getToken() !== null);
    }

    /**
     * @return null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param null $token
     *
     * @return mixed|string|null
     */
    public function setToken($token = null)
    {
        if ($token === null) {
            $token = $this->headers['token'] ?? null;
            if ($this->request !== null) {
                $token = $this->request->bearerToken();
            }
        }
        $this->token = $token;
        return $this;
    }

    /**
     *
     */
    protected function resetData()
    {
        $this->withProxyResponse = false;
        $this->request = null;
        $this->method = null;
        $this->headers = ["Accept" => "application/json"];
        $this->path = null;
        $this->data = [];
        $this->token = null;
        $this->modelId = null;
        $this->serviceUrl = null;
        $this->dispatch = false;
        $this->service = null;
    }
}
