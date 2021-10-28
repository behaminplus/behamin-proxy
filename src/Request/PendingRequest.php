<?php


namespace Behamin\ServiceProxy\Request;


use Behamin\ServiceProxy\Response\ResponseWrapper;
use Behamin\ServiceProxy\UrlGenerator;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class PendingRequest extends \Illuminate\Http\Client\PendingRequest
{

    private RequestInfo $requestInfo;

    public function __construct(RequestInfo $serviceInterface, $factory = null)
    {
        parent::__construct($factory);
        $this->requestInfo = $serviceInterface;
    }

    public function request(Request $request): ResponseWrapper
    {
        $path = $request->path();

        $this->withHeaders(array_merge(
            config('bsproxy.global_headers', []),
            $request->headers->all()
        ));
        $this->withOptions($request->all());
        $this->pendingFiles = $request->files;

        switch ($request->method()) {
            case Request::METHOD_GET:
                return $this->get($path);
            case Request::METHOD_POST:
                return $this->post($path);
            case Request::METHOD_DELETE:
                return $this->delete($path);
            case Request::METHOD_HEAD:
                return $this->head($path);
            case Request::METHOD_PATCH:
                return $this->patch($path);
            case Request::METHOD_PUT:
                return $this->put($path);
            default:
                throw new NotAcceptableHttpException();
        }
    }

    public function get(string $url = null, $query = null)
    {
        $result = parent::get($this->fullUrl($url), $query);
        return $this->respond($result);
    }

    public function delete($url = null, $data = [])
    {
        $result = parent::delete($this->fullUrl($url), $data);
        return $this->respond($result);
    }

    public function head(string $url = null, $query = null)
    {
        $result = parent::head($this->fullUrl($url), $query);
        return $this->respond($result);
    }

    public function patch($url, $data = [])
    {
        $result = parent::patch($this->fullUrl($url), $data);
        return $this->respond($result);
    }

    public function post(string $url, array $data = [])
    {
        $result = parent::post($this->fullUrl($url), $data);
        return $this->respond($result);
    }

    public function put($url, $data = [])
    {
        $result = parent::put($this->fullUrl($url), $data);
        return $this->respond($result);
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

        return $baseUrl.($servicePath === '' ? $servicePath : '/'.$servicePath).'/'.$finalPath;
    }

    private function respond($result)
    {
        if ($result instanceof PromiseInterface) {
            return $result;
        }
        return new ResponseWrapper($result, $this->requestInfo);
    }
}
