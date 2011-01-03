<?php

namespace Spark\Router;

class RestRoute implements Route
{
    protected $method;
    protected $routeSpec;
    protected $defaults;
    protected $callback;    
    
    protected $urlDelimiter = "/";
    protected $urlParam     = ":";
    
    protected $params = array();
    protected $parts  = array();
    protected $staticCount = 0;
    
    protected $wildcardData = array();
    
    function __construct($method = "GET", $routeSpec, $callback, Array $defaults = array())
    {
        $this->method    = $method;
        $this->routeSpec = trim($routeSpec, $this->urlDelimiter);
        $this->callback  = $callback;
        $this->defaults  = $defaults;

        $this->parseSpec();
    }    
    
    function match(\Spark\HttpRequest $request)
    {
        if (null !== $this->method and $request->getMethod() !== $this->method) {
            return false;
        }
        
        if (!empty($this->method)) {
            $request->setMetadata("action", strtolower($this->method));
        }
        
        $path   = trim($request->getRequestUri(), $this->urlDelimiter);
        $params = array();
        
        $staticCount = 0;
        
        if ($path !== '') {
            $path = explode($this->urlDelimiter, $path);
            
            foreach ($path as $pos => $pathPart) {
                if (isset($this->params[$pos])) {
                    $params[$this->params[$pos]] = $pathPart;
                    continue;
                }
                if (isset($this->parts[$pos])) {
                    if ($this->parts[$pos] != $pathPart) {
                        return false;
                    }
                }
                $staticCount++;
            }
        }
        
        $params = $params + $this->defaults;
        
        if (sizeof($params) < sizeof($this->params)) {
            return false;
        }
        if ($staticCount !== $this->staticCount) {
            return false;
        }
        
        $params["callback"] = $this->callback;
        
        return $params;
    }
    
    protected function parseSpec()
    {
        if ($this->params and $this->parts) {
            return;
        }
        $parts = explode($this->urlDelimiter, $this->routeSpec);
        
        foreach ($parts as $pos => $part) {
            if (substr($part, 0, 1) == $this->urlParam and substr($part, 1, 1) != $this->urlParam) {
                $part = substr($part, 1);
                $this->params[$pos] = $part;
                continue;
            }
            
            if (substr($part, 0, 1) == $this->urlParam) {
                $part = substr($part, 1);
            }
            if (empty($part)) {
                continue;
            }
            $this->parts[$pos] = $part;
            $this->staticCount++;
        }
    }
}
