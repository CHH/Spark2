<?php

namespace Spark;

class HaltException extends \RuntimeException implements Exception
{
    protected $response;    
    
    function __construct(\Spark\Http\Response $response)
    {
        $this->response = $response;
    }
    
    function getResponse()
    {
        return $this->response;
    }
    
    function send()
    {   
        $this->response->send();
    }
}
