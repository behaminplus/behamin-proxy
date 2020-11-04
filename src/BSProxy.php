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
 */
class BSProxy extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(){
        return Proxy::class;
    }
}