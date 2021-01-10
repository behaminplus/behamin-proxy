<?php

namespace BSProxy;

use BSProxy\Exceptions\ServiceProxyException;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Client\Response;
use phpDocumentor\Reflection\DocBlock\Serializer;

class BSProxyResponse implements Responsable
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
    protected $addResponseToException = false;

    public function setRetException()
    {
        $this->retException = true;
    }

    public function __construct($response, $proxy)
    {
        $this->response = $response;
        if ($response instanceof Response) {
            $this->body = (string)$response->getBody();
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
                        (strtolower($this->proxy->getService())) . "\n" . substr($this->body, 0, 1000)
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
    function successful(){
        $statusCode = $this->response->getStatusCode();
        if (empty($this->successStatusCode)) {
            return ($statusCode >= 200 && $statusCode < 300);
        }
        return $this->successStatusCode == $statusCode;
    }

    // Determine if the status code is >= 400...
    public function failed(){
        if ($this->successful()){
            return false;
        }
        return $this->response->getStatusCode() >= 400;
    }

    public function withException($exceptionStack = ['proxy' => 'request failed, please check errors if exists or proxy info.'])
    {
        if ($this->getStatusCode() != $this->successStatusCode) {
            $exceptionStack = is_array($exceptionStack) ? $exceptionStack : [$exceptionStack];
            if ($this->addInfoToException) {
                $exceptionStack['info'] = $this->getInfo();
            }
            if ($this->addResponseToException) {
                if ($this->hasError()) {
                    $exceptionStack['error_response'] = $this->getArrayErrors();
                } else {
                    $exceptionStack['response'] = json_decode($this->getBody());
                }
            }
            throw new ServiceProxyException( 'request from ' . $this->getProxy()->getService() . ' service failed.', $this->getStatusCode(), $exceptionStack);
        }
        return $this;
    }

    public function withResponseInException()
    {
        $this->addResponseToException = true;
        return $this;
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
        if (empty($this->bodyJson->error) || ! $this->bodyJson->error) {
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
        return (array)$this->getErrors();
    }

    public function getItems()
    {
        if ($this->hasItems()) {
            return $this->bodyJson->data->items;
        } else {
            if ($this->retException) {
                throw new ServiceProxyException(
                    'data->items object not exists from ' .
                        (strtolower($this->proxy->getService())) . "\n" . substr($this->body, 0, 1000)
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
        return [
            'url' => $this->getProxy()->getServiceRequestUrl(),
            'method' => $this->getProxy()->getMethod(),
            'data' => $this->getProxy()->getData(),
            'statusCode' => $this->getStatusCode(),
            'content' => html_entity_decode(substr($this->body, 0, 1000)),
        ];
    }

    public function hasItem($offset = null) {
        if ($offset === null) {
            return isset($this->bodyJson->data, $this->bodyJson->data->item);
        } else {
            if (! $this->hasItems()){
                return false;
            }
            $items = $this->getItems();
            if (is_array($items) && isset($items[$offset])){
                return true;
            }
            return false;
        }
    }

    public function hasItems(){
        return isset($this->bodyJson->data, $this->bodyJson->data->items);
    }

    public function toJson($options = 0, $items = false)
    {
        if ($items && $this->hasItems()){
            return json_encode($this->getItems(), $options);
        }
        if ($items && $this->hasItem()) {
            return json_encode($this->getItem(), $options);
        }
        return $this->response->json();
    }

    public function toResponse($request){

        return (new \Illuminate\Http\Response($this->body, $this->getStatusCode(), $this->response->headers()));
    }
}
