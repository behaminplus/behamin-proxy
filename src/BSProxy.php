<?php
namespace BSProxy;

/**
 * Class BSProxy
 * @package BSProxy
 * @method static string makeRequest($request, $service, $method = 'get', $path = null, $modelId = null, $data = [], $headers = [])
 * @method static Proxy setData($data)
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