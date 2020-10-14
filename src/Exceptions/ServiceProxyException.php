<?php
namespace BSProxy\Exceptions;

use Exception;

class ServiceProxyException extends Exception
{
    private $_options;
    private $_next;

    public function __construct(
        $message,
        $code = 0,
        Exception $previous = null,
        $options = array('params'),
        $next = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->_options = $options;
        $this->_next = $next;
    }

    public function GetOptions()
    {
        return $this->_options;
    }

    public function getNext(){
        return $this->_next;
    }

}
