<?php

namespace SparkCore\Http;

interface RequestInterface
{
    /**
     * Used to set metadata on the request object
     *
     * If $spec and $value are null, then it should return all metadata.
     * If $spec is scalar and the value is null, then the $spec should be treated
     * as key and the value should be returned.
     *
     * @param array|string $spec Treat as meta => value pairs if given as array
     * @param mixed $value
     */
    function meta($spec = null, $value = null);
    
    /**
     * @param callback $callback
     */
    function setCallback($callback);
    function getCallback();
    
    function setDispatched($dispatched = true);
    function isDispatched();
    
    function getMethod();
    function getRequestUri();
    
    function query($key = null);
    function post($key = null);
    function file($key = null);
    function cookie($key = null);
    function server($key = null);
    function env($key = null);
    
    function header($key);
}
