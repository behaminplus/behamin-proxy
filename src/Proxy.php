<?php

namespace Behamin\ServiceProxy;

use Behamin\ServiceProxy\Exceptions\ServiceProxyException;
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
    private $headers = [];
    private $path = null;
    private $data = [];
    private $token = null;
    private $modelId = null;
    private $serviceUrl = null;
    private $service = null;
    private $files = null;
    private $dispatch = false;
    private $withProxyResponse = false;
    private $breakOnError = true;
    private $numberOfAttempts = 1;
    private $sleepBetweenAttempts = 0;

    /**
     * @param Request|null $request
     * @param string $service
     * @param ?string $method
     * @param ?string $path
     * @param ?int $modelId
     * @param array $data
     * @param array $headers
     *
     * @return mixed|BSProxyResponse
     * @throws ServiceProxyException
     * @deprecated
     */
    public function makeRequest(
        ?Request $request,
        string $service,
        ?string $method = null,
        ?string $path = null,
        ?int $modelId = null,
        array $data = [],
        array $headers = []
    )
    {
        $this->setService($service);

        if (!empty($headers)) {
            $this->addHeaders($headers);
        }
        if ($path !== null) {
            $this->setPath($path);
        }
        if ($modelId !== null) {
            $this->setModelId($modelId);
        }
        if (!empty($method)) {
            $this->setMethod($method);
        } elseif ($request === null and empty($this->getMethod())) {
            $this->setMethod('get');
        }
        if (!empty($data)) {
            $this->setData($data);
        }
        if (empty($this->getRequest()) and $request !== null) {
            $this->setRequest($request);
        }

        $headers = $this->getHeaders();
        $response = Http::withHeaders($headers);
        $numberOfAttempts = $this->getNumberOfAttempts();
        if ($numberOfAttempts > 1) {
            $response = $response->retry($numberOfAttempts, $this->getSleepBetweenAttempts());
        }
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

        if (empty($request) && $this->files) {
            foreach ($this->files as $name => $file) {
                $response = $response->attach(
                    $name,
                    $this->getFileContent($name),
                    $this->getFileOriginalName($name)
                );
            }
        }

        $thisProxy = clone $this;
        if ($this->isDispatchRequest()) {
            $response = $this->dispatchRequest();
            return $this->getProxyResponse($response, $thisProxy);
        } else {
            $method = strtolower($this->getMethod());
            $response = $response->{$method}(
                $this->getServiceRequestUrl(),
                $this->getData()
            );
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
     * @param $service
     *
     *
     * @return BSProxyResponse|mixed
     * @throws ServiceProxyException
     */
    public function request($service)
    {
        return $this->makeRequest($this->getRequest(), $service);
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
            if ($this->breakOnError()) {
                throw new ServiceProxyException(
                    $errorMessage,
                    $response->status(),
                    $errors
                );
            }
        }
    }

    /**
     * @param $jsonResponse
     *
     * @return bool
     */
    private function responseHasError($jsonResponse): bool
    {
        return is_array($jsonResponse) and
            (array_key_exists('error', $jsonResponse) or array_key_exists('message', $jsonResponse));
    }

    /**
     * @param $jsonResponse
     *
     * @return mixed
     */
    private function getResponseMessage($jsonResponse)
    {
        if (array_key_exists('error', $jsonResponse) and
            (is_array($jsonResponse['error']) and
                array_key_exists('message', $jsonResponse['error']))) {
            return $jsonResponse['error']['message'];
        }
        if (array_key_exists('error', $jsonResponse) and is_string($jsonResponse['error'])) {
            return $jsonResponse['error'];
        }
        if (array_key_exists('message', $jsonResponse)) {
            return $jsonResponse['message'];
        }
        return null;
    }

    /**
     * @param $jsonResponse
     *
     * @return mixed
     */
    private function getResponseError($jsonResponse)
    {
        if (array_key_exists('error', $jsonResponse) and
            (is_array($jsonResponse['error']) and array_key_exists('errors', $jsonResponse['error']))) {
            return $jsonResponse['error']['errors'];
        }
        if (array_key_exists('trace', $jsonResponse)) {
            return $jsonResponse['trace'];
        }
        if (array_key_exists('errors', $jsonResponse) and is_array($jsonResponse['errors'])) {
            return $jsonResponse['errors'];
        }
        return null;
    }

    /**
     * @param $name string file name in request
     * @param $file array|UploadedFile
     *
     * @return $this
     */
    public function addFile($name, $file): Proxy
    {
        if (!is_array($file) and !($file instanceof UploadedFile)) {
            throw new InvalidArgumentException(
                'An uploaded file must be an array or an instance of UploadedFile.'
            );
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

    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param $nameInRequest
     *
     * @return mixed
     */
    private function getFileOriginalName($nameInRequest)
    {
        if (!empty($this->files[$nameInRequest])) {
            return $nameInRequest;
        }

        $file = $this->files[$nameInRequest];

        if (is_array($file)) {
            return $file['name'];
        }

        if ($file instanceof UploadedFile) {
            return $file->getClientOriginalName();
        }
        return $nameInRequest;
    }

    /**
     * @param $nameInRequest
     *
     * @return false|resource
     * @throws RuntimeException
     */
    private function getFileContent($nameInRequest)
    {
        if (empty($this->files[$nameInRequest])) {
            return false;
        }

        $file = $this->files[$nameInRequest];

        if (is_array($file)) {
            return fopen($file['tmp_name'], 'r');
        }

        if ($file instanceof UploadedFile) {
            return file_get_contents($file->getPathname());
        }
        return false;
    }


    /**
     * @param $response
     * @param $thisProxy
     *
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
    public function withProxyResponse(): Proxy
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
     *
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
        $request = Request::create(
            $this->getServiceRequestUrl(),
            $this->getMethod(),
            $this->getData()
        );
        if ($this->files) {
            $request->files = new FileBag($this->files);
        }
        return app()->handle($request);
    }

    /**
     * @param $response
     *
     * @return bool
     */
    private function isBadRequest($response): bool
    {
        return $response->status() >= 400;
    }

    protected function isDispatchRequest(): bool
    {
        return $this->dispatch;
    }

    /**
     * @return $this
     */
    public function setDispatch(): Proxy
    {
        $this->dispatch = true;
        return $this;
    }

    /**
     * @return string
     * @throws ServiceProxyException
     */
    public function getServiceRequestUrl()
    {
        $serviceUrl = trim($this->getServiceUrl(), '/') . '/';
        $pathHaveQueryString = false;
        if ($path = trim($this->getPath(), '/')) {
            $pathHaveQueryString = Str::contains($path, '?');
            if (!$pathHaveQueryString) {
                $path .= '/';
            }
        }
        $modelId = trim($this->getModelId(), '/');
        if (!empty($modelId) && $pathHaveQueryString) {
            throw new ServiceProxyException(
                "can't set model id when path includes query string."
            );
        }

        return $serviceUrl . $path . $modelId;
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
     *
     * @return Proxy
     * @throws ServiceProxyException
     */
    public function setService($service, $app = 'global_app_url')
    {
        if (is_array($service)) {
            [$service, $app] = array_values($service);
        }
        $this->service = $service;
        return $this->setServiceUrl($service, $app);
    }

    /**
     * @return string|null
     */
    public function getModelId()
    {
        if ($this->modelId !== null) {
            return $this->modelId;
        }
        return '';
    }

    /**
     * @param  $modelId
     *
     * @return Proxy
     */
    public function setModelId($modelId): Proxy
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
     *
     * @return Proxy
     * @throws ServiceProxyException
     */
    public function setServiceUrl($service, $baseUrl = 'global_app_url'): Proxy
    {
        $parsedUrl = parse_url(config('bsproxy.' . $baseUrl));
        if (empty($parsedUrl['host'])) {
            throw new ServiceProxyException(
                'host address not found in the config file.'
            );
        }

        $scheme = ($parsedUrl['scheme'] ?? 'https') . '://';
        $host = $parsedUrl['host'];

        $port = '';
        if (!empty($parsedUrl['port'])) {
            $port = ':' . $parsedUrl['port'];
        }

        $path = $parsedUrl['path'] ?? '';
        $path .= config('bsproxy.service_urls.' . $service, null);
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
    public function setMethod(string $method): Proxy
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
    public function setRequest($request): Proxy
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
            if (empty($this->getMethod())) {
                $this->setMethod($request->method());
            }
            if ($this->getPath() === null) {
                $this->setPath($request->path());
            }
            if (empty($this->getData())) {
                $this->setData($request->all());
            }
            if (!empty($token = $this->request->bearerToken())) {
                $this->token = $token;
            }
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
        return array_merge(
            $this->headers,
            config('bsproxy.global_headers', [])
        );
    }

    protected function getNumberOfAttempts(): int
    {
        return $this->numberOfAttempts;
    }

    protected function getSleepBetweenAttempts(): int
    {
        return ($this->sleepBetweenAttempts > 0) ? $this->sleepBetweenAttempts : 0;
    }

    public function setRetry(int $attempts, int $sleepInMilliseconds)
    {
        $this->numberOfAttempts = $attempts;
        $this->sleepBetweenAttempts = $sleepInMilliseconds;
    }

    /**
     * @param string[] $headers
     *
     * @return Proxy
     */
    public function setHeaders(array $headers): Proxy
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param $header
     *
     * @return $this
     */
    public function addHeaders(array $headers): Proxy
    {
        $this->headers = array_merge($this->headers, $headers);
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
    public function setData($data): Proxy
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
     * @param string|null $token
     *
     * @return mixed|string|null
     */
    public function setToken(?string $token): Proxy
    {
        $this->token = $token;
        return $this;
    }

    public function withoutException(): Proxy
    {
        $this->breakOnError = false;
        return $this;
    }

    private function breakOnError(): bool
    {
        return $this->breakOnError;
    }

    /**
     *
     */
    protected function resetData()
    {
        $this->withProxyResponse = false;
        $this->breakOnError = true;
        $this->request = null;
        $this->method = null;
        $this->headers = [];
        $this->path = null;
        $this->data = [];
        $this->token = null;
        $this->modelId = null;
        $this->serviceUrl = null;
        $this->dispatch = false;
        $this->service = null;
        $this->numberOfAttempts = 1;
        $this->sleepBetweenAttempts = 0;
    }
}
