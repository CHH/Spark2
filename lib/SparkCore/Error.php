<?php

namespace SparkCore;

use Exception,
    SparkCore\Http\Request;

class Error
{
    protected $msg;
    protected $exception;
    protected $request;
    
    function __construct($msg, RequestInterface $request, Exception $exception = null)
    {
        $this->msg = $msg;
        $this->request = $request;
        $this->exception = $exception;
    }
    
    function getMessage()
    {
        return $this->msg;
    }
    
    function getRequest()
    {
        return $this->request;
    }
    
    function getException()
    {
        return $this->exception;
    }
}
