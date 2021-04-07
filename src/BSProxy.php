<?php

namespace Behamin\ServiceProxy;

use Illuminate\Support\Facades\Facade;

/**
 * Class BSProxy
 * @package BSProxy
 * @method static BSProxyResponse|mixed makeRequest($request, string $service, ?string $method = null, ?string $path = null, ?int $modelId = null, array $data = [], array $headers = [])
 * @method static BSProxyResponse|mixed request(string $service)
 * @method static setService($service, $app = 'global_app_url')
 * @method static Proxy setData(array $data)
 * @method static Proxy addHeaders(array $headers)
 * @method static Proxy setToken($token)
 * @method static Proxy setPath(string $path)
 * @method static Proxy setRequest($request)
 * @method static Proxy setMethod(string $method)
 * @method static Proxy setModelId(?int $modelId)
 * @method static mixed|BSProxyResponse dispatch($serviceName)
 * @method static Proxy addFile($name, $file) send file with $name in request
 * @method static string getServiceRequestUrl()
 * @method static Proxy withoutException()
 * @method static Proxy withProxyResponse()
 */
class BSProxy extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Proxy::class;
    }
}
