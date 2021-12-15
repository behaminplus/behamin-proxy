# Service Proxy

Internal communication between services with useful tools
<br>
Make request by laravel http client

## Installation

```bash
composer require behamin/service-proxy
```

### Publish config

```bash
php artisan vendor:publish --provider="Behamin\ServiceProxy\Providers\ProxyServiceProvider" --tag config
```

### Add services

Add your project's base url and global headers in `proxy.php` config

```php
return [
    /**
     * Headers added to every request
     */
    'global_headers' => [
        'Accept' => 'application/json',
        ...
    ],

    'base_url' => env('PROXY_BASE_URL', env('APP_URL')),
];
```

## Usage

### Normal usage

```php
use Behamin\ServiceProxy\Proxy;

// Http Get
Proxy::withToken('Your bearer token')
    ->acceptJson()
    ->retry(3)
    ->withHeaders([
        "Content-Type" => "application\json"
    ])->get('api/articles');
    
Proxy::post('api/articles', [
    "title" => "Test title",
    "body" => "Test body"
]);

Proxy::patch('api/articles/1', [
    "title" => "Test title",
    "body" => "Test body"
]);

Proxy::put('api/articles', [
    "title" => "Test title",
    "body" => "Test body"
]);

Proxy::delete('api/articles/1');
```

### Using http request

```php
use Behamin\ServiceProxy\Proxy;
use Illuminate\Http\Request;

public function index(Request $request) {
    $serviceName = 'test-service';
    Proxy::request($request, $serviceName);
}
```

### Proxy events

#### On success

```php
use Behamin\ServiceProxy\Proxy;
use Behamin\ServiceProxy\Responses\ProxyResponse;
 
Proxy::get('api/articles/1')->onSuccess(function (ProxyResponse $responseWrapper) {
        $data = $responseWrapper->data();
        $message = $responseWrapper->message();
        $response = $responseWrapper->response();
        $items = $responseWrapper->items();
        $count = $responseWrapper->count();
        ...
    });
```

#### On error
```php
use Behamin\ServiceProxy\Proxy;
use Behamin\ServiceProxy\Exceptions\ProxyException;
 
Proxy::get('api/articles/1')->onSuccess(function (ProxyException $proxyException) {
        $responseWrapper = $proxyException->responseWrapper;
        $trace = $proxyException->getTraceAsString();
        ...
    });
```

#### On data success
```php
use Behamin\ServiceProxy\Proxy;
 
Proxy::get('api/articles/1')->onDataSuccess(function (array $data) {
        $id = $data['id'];
    });
```

#### On data collection success
```php
use Behamin\ServiceProxy\Proxy;
 
Proxy::get('api/articles/1')->onCollectionSuccess(function (array $items, int $count) {
        ...
    });
```


### Response wrapper methods
```php
use Behamin\ServiceProxy\Proxy;

$responseWrapper = Proxy::get('api/articles/1');
```

| Method                        | Description                                    |
| ----------------------------- | ---------------------------------------------- |
| data()                        | given data                                     |
| items()                       | give items                                     |
| count()                       | given items count                              |
| errors()                      | given errors if there is                       |
| message()                     | given message                                  |
| onSuccess($closure)           | When http request is successful                |
| onError($closure)             | When http request is with error                |
| onCollectionSuccess($closure) | Get collection when http request is successful |
| onDataSuccess($closure)       | Get data when http request is successful       |
| throw()                       | Throw error if http request failed             |
| toException()                 | Get exception if http request failed           |

### Proxy request methods

| Method                        | Return Type                                    |
| ----------------------------- | ---------------------------------------------- |
fake($callback = null) | \Illuminate\Http\Client\Factory
accept(string $contentType) | \Behamin\ServiceProxy\Http 
acceptJson() | \Behamin\ServiceProxy\Http 
asForm() | \Behamin\ServiceProxy\Http 
asJson() | \Behamin\ServiceProxy\Http 
asMultipart() | \Behamin\ServiceProxy\Http 
async() | \Behamin\ServiceProxy\Http 
attach(string array $name, string $contents = '', string null $filename = null, array $headers = []) | \Behamin\ServiceProxy\Http 
baseUrl(string $url) | \Behamin\ServiceProxy\Http 
beforeSending(callable $callback) | \Behamin\ServiceProxy\Http 
bodyFormat(string $format) | \Behamin\ServiceProxy\Http 
contentType(string $contentType) | \Behamin\ServiceProxy\Http 
dd() | \Behamin\ServiceProxy\Http 
dump() | \Behamin\ServiceProxy\Http 
retry(int $times, int $sleep = 0) | \Behamin\ServiceProxy\Http 
sink(string|resource $to) | \Behamin\ServiceProxy\Http 
stub(callable $callback) | \Behamin\ServiceProxy\Http 
timeout(int $seconds) | \Behamin\ServiceProxy\Http 
withBasicAuth(string $username, string $password) | \Behamin\ServiceProxy\Http 
withBody(resource|string $content, string $contentType) | \Behamin\ServiceProxy\Http 
withCookies(array $cookies, string $domain) | \Behamin\ServiceProxy\Http 
withDigestAuth(string $username, string $password) | \Behamin\ServiceProxy\Http 
withHeaders(array $headers) | \Behamin\ServiceProxy\Http 
withMiddleware(callable $middleware) | \Behamin\ServiceProxy\Http 
withOptions(array $options) | \Behamin\ServiceProxy\Http 
withToken(string $token, string $type = 'Bearer') | \Behamin\ServiceProxy\Http 
withUserAgent(string $userAgent) | \Behamin\ServiceProxy\Http 
withoutRedirecting() | \Behamin\ServiceProxy\Http 
withoutVerifying() | \Behamin\ServiceProxy\Http 
pool(callable $callback) | array
request(Request $request, string $service) | \Behamin\ServiceProxy\Responses\ProxyResponse 
get(string $url, array|string|null $query = null) | \Behamin\ServiceProxy\Responses\ProxyResponse 
delete(string $url, array $data = []) | \Behamin\ServiceProxy\Responses\ProxyResponse 
head(string $url, array|string|null $query = null) | \Behamin\ServiceProxy\Responses\ProxyResponse 
patch(string $url, array $data = []) | \Behamin\ServiceProxy\Responses\ProxyResponse 
post(string $url, array $data = []) | \Behamin\ServiceProxy\Responses\ProxyResponse 
put(string $url, array $data = []) | \Behamin\ServiceProxy\Responses\ProxyResponse 
send(string $method, string $url, array $options = []) | \Behamin\ServiceProxy\Responses\ProxyResponse 
fakeSequence(string $urlPattern = '*') | \Illuminate\Http\Client\ResponseSequence
assertSent(callable $callback) | void 
assertNotSent(callable $callback) | void 
assertNothingSent() | void 
assertSentCount(int $count) | void 
assertSequencesAreEmpty() | void
