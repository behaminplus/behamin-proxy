<?php


namespace Behamin\ServiceProxy\Request;


use Behamin\ServiceProxy\UrlGenerator;
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
        return parent::get($this->fullUrl($url), $query);
    }

    public function delete($url = null, $data = [])
    {
        $this->prepare();
        return parent::delete($this->fullUrl($url), $data);
    }

    public function head(string $url = null, $query = null)
    {
        $this->prepare();
        return parent::head($this->fullUrl($url), $query);
    }

    public function patch($url, $data = [])
    {
        $this->prepare();
        return parent::patch($this->fullUrl($url), $data);
    }

    public function post(string $url, array $data = [])
    {
        $this->prepare();
        return parent::post($this->fullUrl($url), $data);
    }

    public function put($url, $data = [])
    {
        $this->prepare();
        return parent::put($this->fullUrl($url), $data);
    }


    private function prepare()
    {
        $this->withHeaders(array_merge(
            $this->requestInfo->getHeaders(),
            config('bsproxy.global_headers', [])
        ));
        $this->withOptions($this->requestInfo->getOptions());
    }

    /**
     * @param  null  $path
     * @param  null  $baseUrl
     * @return string
     * @throws \Exception
     */
    private function fullUrl($path = null, $baseUrl = null): string
    {
        if ($baseUrl == null) {
            $baseUrl = UrlGenerator::baseUrl();
        }
        $servicePath = UrlGenerator::getConfigServicePath($this->requestInfo->getService());
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

}
