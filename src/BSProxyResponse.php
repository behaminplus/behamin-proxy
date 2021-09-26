<?php

namespace Behamin\ServiceProxy;

use Behamin\ServiceProxy\Exceptions\ServiceProxyException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Client\Response;

class BSProxyResponse implements \ArrayAccess, Responsable
{
    /**
     * @var $response Response
     */
    protected $response;
    protected $body;
    protected $bodyJson = null;
    /**
     * @var $proxy Proxy
     */
    protected $proxy;
    protected $retException = false;
    protected $successStatusCode;
    protected $addInfoToException = false;

    public function setRetException()
    {
        $this->retException = true;
    }

    public function __construct($response, $proxy)
    {
        $this->response = $response;
        if ($response instanceof Response) {
            $this->body = (string) $response->getBody();
        } else {
            $this->body = $response->getContent();
        }
        $this->bodyJson = json_decode($this->body);
        $this->proxy = $proxy;
    }

    public function getItem($objectName = '*')
    {
        if (isset($this->bodyJson->data)) {
            if ($objectName !== '*') {
                if (property_exists($this->bodyJson->data, $objectName)) {
                    return $this->bodyJson->data->{$objectName};
                } else {
                    return null;
                }
            }
            return $this->bodyJson->data;
        } else {
            if ($this->retException) {
                throw new ServiceProxyException(
                    'data object not exists from ' .
                    (strtolower($this->proxy->getService())) . "\n" . substr($this->body, 0, 1000),
                    $this->proxy->getServiceRequestUrl(),
                    $this->proxy->getService()
                );
            }
            return null;
        }
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    // Determine if the status code is >= 200 and < 300...
    public function successful()
    {
        $statusCode = $this->response->getStatusCode();
        if (empty($this->successStatusCode)) {
            return ($statusCode >= 200 && $statusCode < 300);
        }
        return $this->successStatusCode == $statusCode;
    }

    // Determine if the status code is >= 400...
    public function failed()
    {
        if ($this->successful()) {
            return false;
        }
        return $this->response->getStatusCode() >= 400;
    }

    public function withException($exceptionStack = ['proxy' => 'request failed, please check errors if exists or proxy info.'])
    {
        if ($this->successful()) {
            return $this;
        }

        $exceptionStack = is_array($exceptionStack) ? $exceptionStack : [$exceptionStack];
        if ($this->addInfoToException) {
            $exceptionStack['info'] = $this->getInfo();
        }

        throw new ServiceProxyException(
            ($this->hasError() ? implode(', ', $this->getErrors()) : ''),
            $this->proxy->getServiceRequestUrl(),
            $this->proxy->getService(),
            $this->getStatusCode(),
            $exceptionStack
        );
    }

    public function withInfo()
    {
        $this->addInfoToException = true;
        return $this;
    }

    public function successWhen($code)
    {
        $this->successStatusCode = $code;
        return $this;
    }

    public function hasError($key = null)
    {
        if (empty($this->bodyJson->error) || !$this->bodyJson->error) {
            return false;
        }

        $errors = data_get($this->bodyJson->error, 'errors');
        if (!empty($errors)) {
            if ($key != null) {
                if (!empty($errors->{$key})) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        }
    }

    public function getErrors()
    {

        if (!empty($this->bodyJson->error->errors)) {
            return $this->bodyJson->error->errors;
        }
        return null;
    }

    public function getArrayErrors()
    {
        return (array) $this->getErrors();
    }

    public function getItems()
    {
        if ($this->hasItems()) {
            return $this->bodyJson->data->items;
        } else {
            if ($this->retException) {
                throw new ServiceProxyException(
                    'data->items object not exists from ' .
                    (strtolower($this->proxy->getService())) . "\n" . substr($this->body, 0, 1000),
                    $this->proxy->getServiceUrl(),
                    $this->proxy->getService()
                );
            }
            return null;
        }
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    public function getInfo()
    {

        $info = [
            'url' => $this->getProxy()->getServiceRequestUrl(),
            'method' => $this->getProxy()->getMethod(),
            'data' => $this->getProxy()->getData(),
            'statusCode' => $this->getStatusCode(),
        ];

        if ($this->hasError()) {
            $info['error_response'] = $this->getArrayErrors();
        } else {
            $info['response'] = json_decode($this->getBody());
        }

        if (!empty($info['response']->trace) && is_array($info['response']->trace)) {
            $info['response']->trace = array_slice($info['response']->trace, 0, 5);
        }

        return $info;
    }

    public function hasItem($offset = null)
    {
        if ($offset === null) {
            return isset($this->bodyJson->data, $this->bodyJson->data->item);
        } else {
            if (!$this->hasItems()) {
                return false;
            }
            $items = $this->getItems();
            if (is_array($items) && isset($items[$offset])) {
                return true;
            }
            return false;
        }
    }

    public function hasItems()
    {
        return isset($this->bodyJson->data, $this->bodyJson->data->items);
    }

    public function toJson($options = 0, $items = false)
    {
        if ($items && $this->hasItems()) {
            return json_encode($this->getItems(), $options);
        }
        if ($items && $this->hasItem()) {
            return json_encode($this->getItem(), $options);
        }

        return $this->response->body();
    }

    public function toResponse($request)
    {

        return \response()->json($this->bodyJson)->setStatusCode($this->getStatusCode());
    }

    public function offsetExists($offset)
    {
        return $this->hasItem($offset);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->getItems()[$offset];
        }
        return null;
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

}
