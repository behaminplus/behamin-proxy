<?php


namespace Behamin\ServiceProxy\Requests;


use Behamin\ServiceProxy\Responses\ResponseWrapper;
use Behamin\ServiceProxy\UrlGenerator;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class PendingRequest extends \Illuminate\Http\Client\PendingRequest
{
    private string $service = '';

    public function __construct($factory = null)
    {
        parent::__construct($factory);
    }

    public function request(Request $request, string $service): ResponseWrapper
    {
        $this->service = $service;
        $path = $request->path();
        $data = $request->all();

        foreach ($request->allFiles() as $name => $file) {
            unset($data[$name]);
            $this->attach(
                $name,
                $request->file($name)->getContent(),
                $request->file($name)->getClientOriginalName()
            );
        }

        switch ($request->method()) {
            case Request::METHOD_GET:
                return $this->get($path, $data);
            case Request::METHOD_POST:
                return $this->post($path, $data);
            case Request::METHOD_DELETE:
                return $this->delete($path, $data);
            case Request::METHOD_HEAD:
                return $this->head($path, $data);
            case Request::METHOD_PATCH:
                return $this->patch($path, $data);
            case Request::METHOD_PUT:
                return $this->put($path, $data);
            default:
                throw new NotAcceptableHttpException();
        }
    }

    public function get(string $url = null, $query = null)
    {
        $this->prepare();
        $result = parent::get($this->fullUrl($url), $query);
        return $this->respond($result);
    }

    public function delete($url = null, $data = [])
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

    public function patch($url, $data = [])
    {
        $this->prepare();
        $result = parent::patch($this->fullUrl($url), $data);
        return $this->respond($result);
    }

    public function post(string $url, array $data = [])
    {
        $this->prepare();
        $result = parent::post($this->fullUrl($url), $data);
        return $this->respond($result);
    }

    public function put($url, $data = [])
    {
        $this->prepare();
        $result = parent::put($this->fullUrl($url), $data);
        return $this->respond($result);
    }

    public function prepare()
    {
        $this->withHeaders(
            config('proxy.global_headers', []),
        );
    }

    /**
     * @param  null  $path
     * @return string
     */
    private function fullUrl($path): string
    {
        $baseUrl = UrlGenerator::baseUrl();
        $servicePath = $this->service;

        if (Str::endsWith($baseUrl, '/')) {
            $baseUrl = Str::substr($baseUrl, 0, -1);
        }

        if (Str::startsWith($servicePath, '/')) {
            $servicePath = Str::substr($baseUrl, 1);
        }

        if (Str::startsWith($path, '/')) {
            $finalPath = Str::substr($path, 1);
        } else {
            $finalPath = $path;
        }

        return $baseUrl.($servicePath === '' ? $servicePath : '/'.$servicePath).'/'.$finalPath;
    }

    private function respond($result)
    {
        if ($result instanceof PromiseInterface) {
            return $result;
        }
        return new ResponseWrapper($result);
    }
}
