# bsproxy
internal proxy service with service name and more utilities in the future

## install 
```
composer require behamin/service-proxy
```
### Publish config
```
php artisan vendor:publish --provider="BSProxy/BSProxyServiceProvider" --tag config
```
### Add services
Add your services in `proxy-services-url.php` config
```
return [

    'USER'          => 'user-service/',
    ...

    'GLOBAL_APP_URL' => env('GLOBAL_APP_URL', env('HTTP_HOST'))
]
```
## Usage
```
BSProxy::makeRequest($request, 'SERVICE_NAME', $method, $path)
```
