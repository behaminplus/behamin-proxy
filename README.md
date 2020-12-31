# Service Proxy
Internal communication between services with useful tools
<br>
Make request by dispatch and laravel http client  

## install 
```
composer require behamin/service-proxy
```
### Publish config
```
php artisan vendor:publish --provider="BSProxy\BSProxyServiceProvider" --tag config
```
### Add services
Add your services in `proxy-services-url.php` config
```
return [

    'USER'          => 'user-service',
    ...
    'GLOBAL_APP_URL' => env('GLOBAL_APP_URL', 'https://yourAppUrl.dom')
]
```
## Usage
```
BSProxy::makeRequest(null, 'SERVICE_NAME', $method = 'get', $path = null, $modelId = null, $data=[], $headers=[])
```
Use only with $request variable 
```
BSProxy::makeRequest($request, 'USER', 'POST')
```  
notice if not passed method parameter, override with default value: GET method
<br>
### Another make request by chaining methods
```
$response = PSProxy::withProxyResponse()
            ->setData(
                [
                    'ids' => [1, 2, 3]
                ]
            )
            ->setPath('/products')
            ->setMethod('POST')
            ->makeRequest(null, 'USER');
```

### With Proxy Response
by use ```withProxyResponse``` method, returns an instance of ```BSProxyResponse``` 
which provides a variety methods that may be used to inspect the response 
and get data (item or items) , handle errors with exception.
```
$response->getStatusCode() : int;
$response->successful() : bool;
$response->failed() : bool;
$response->hasError() : bool;
$response->getErrors() : array;
$response->items() : array;
$response->item() : object;
$response->getInfo() : array;
$response->body() : string;
$response->getProxy() : Proxy;
```