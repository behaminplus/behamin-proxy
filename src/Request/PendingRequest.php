<?php


namespace Behamin\ServiceProxy\Request;


use Behamin\ServiceProxy\Response\ResponseWrapper;
use Behamin\ServiceProxy\UrlGenerator;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Str;

class PendingRequest extends \Illuminate\Http\Client\PendingRequest
{

    /**
     * @var RequestInfo
     */
    private RequestInfo $requestInfo;

    public function __construct(RequestInfo $serviceInterface, $factory = null)
    {
        parent::__construct($factory);
        $this->requestInfo = $serviceInterface;
    }

    public function get(string $url = null, $query = null)
    {
        $this->prepare();
        $result = parent::get($this->fullUrl($url), $query);
        return $this->respond($result);
    }

    public function delete($url = null, $data = []): ResponseWrapper
    {
        $this->prepare();
        $result = parent::delete($this->fullUrl($url), $data);
        return $this->respond($result);
    }

    public function head(string $url = null, $query = null)
    {
        $this->prepare();
        $result = parent::head($this->fullUrl($url), $query);
        return $this->respond($result);
    }

    public function patch($url, $data = []): ResponseWrapper
    {
        $this->prepare();
        $result = parent::patch($this->fullUrl($url), $data);
        return $this->respond($result);
    }

    public function post(string $url, array $data = []): ResponseWrapper
    {
        $this->prepare();
        $result = parent::post($this->fullUrl($url), $data);
        return $this->respond($result);
    }

    public function put($url, $data = []): ResponseWrapper
    {
        $this->prepare();
        $result = parent::put($this->fullUrl($url), $data);
        return $this->respond($result);
    }


    private function prepare()
    {
        $this->withHeaders(array_merge(
            config('bsproxy.proxy_base_url', []),
            $this->requestInfo->getHeaders()
        ));
        $this->withOptions($this->requestInfo->getOptions());
    }

    /**
     * @param  null  $path
     * @return string
     */
    private function fullUrl($path = null): string
    {
        $baseUrl = UrlGenerator::baseUrl();
        $servicePath = $this->requestInfo->getService();

        if (Str::endsWith($baseUrl, '/')) {
            $baseUrl = Str::substr($baseUrl, 0, -1);
        }

        if (Str::startsWith($servicePath, '/')) {
            $servicePath = Str::substr($baseUrl, 1);
        }

        if ($path == null) {
            if (Str::startsWith($this->requestInfo->getPath(), '/')) {
                $finalPath = Str::substr($this->requestInfo->getPath(), 1);
            } else {
                $finalPath = $this->requestInfo->getPath();
            }
        } else {
            if (Str::startsWith($path, '/')) {
                $finalPath = Str::substr($path, 1);
            } else {
                $finalPath = $path;
            }
        }

        return $baseUrl.'/'.$servicePath.'/'.$finalPath;
    }

    private function respond($result)
    {
        if ($result instanceof PromiseInterface) {
            return $result;
        }
        return new ResponseWrapper($result, $this->requestInfo);
    }
}
