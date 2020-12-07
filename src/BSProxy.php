<?php
namespace BSProxy;

/**
 * Class BSProxy
 * @package BSProxy
 * @method static string|mixed|BSProxyResponse makeRequest($request, $service, $method = 'get', $path = null, $modelId = null, $data = [], $headers = [])
 * @method static setService($service, $app = 'GLOBAL_APP_URL')
 * @method static Proxy setData($data)
 * @method static Proxy withProxyResponse()
 * @method static Proxy addHeader(array $header)
 * @method static Proxy setToken($token = null)
 * @method static Proxy setHeaders(array $headers)
 * @method static Proxy setPath(string $path)
 * @method static Proxy setRequest($request)
 * @method static Proxy setMethod(string $method)
 * @method static Proxy setModelId($modelId)
 * @method static mixed|BSProxyResponse dispatch($serviceName)
 * @method static Proxy addFile($name, $file)
 * @method static string getServiceRequestUrl()
 */
class BSProxy extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(){
        return Proxy::class;
    }
}