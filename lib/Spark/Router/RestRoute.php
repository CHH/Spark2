<?php

namespace Spark\Router;

class RestRoute
{
    protected $method;
    protected $routeSpec;
    protected $options;
    protected $callback;    
    
    protected $urlDelimiter = "/";
    protected $urlParam     = ":";
    
    protected $params = array();
    protected $parts  = array();
    protected $staticCount = 0;
    
    protected $wildcardData = array();
    
    public function __construct($method = "GET", $routeSpec, $callback, Array $options = array())
    {
        $this->method    = $method;
        $this->routeSpec = trim($routeSpec, $this->urlDelimiter);
        $this->callback  = $callback;
        $this->options   = $options;
    }    
    
    public function match(\Spark\Controller\HttpRequest $request)
    {
        if (null !== $this->method and $request->getMethod() !== $this->method) {
            return false;
        }
        $this->parseSpec();
        $path = trim($request->getRequestUri(), $this->urlDelimiter);
        
        if ($path !== '') {
            $path = explode($this->urlDelimiter, $path);
            foreach ($path as $pos => $pathPart) {
                if ($this->parts[$pos] == '*') {
                    // Get wildcard params and stop matching
                    
                    break;
                }
                
            }
            
            
        }
    }
    
    protected function parseSpec()
    {
        if ($this->params and $this->parts) {
            return;
        }
        if (!is_string($this->routeSpec) or empty($this->routeSpec)) {
            throw new \UnexpectedValueException("Route spec can not be empty");
        }
        
        foreach (explode($this->urlDelimiter, $this->routeSpec) as $pos => $part) {
            if (substr($part, 0, 1) == $this->urlParam and substr($part, 1, 1) != $this->urlParam) {
                $part = substr($part, 1);
                $this->params[$pos] = $part;
                continue;
            }
            
            if (substr($part, 0, 1) == $this->_urlVariable) {
                $part = substr($part, 1);
            }

            $this->parts[$pos] = $part;

            if ($part !== '*') {
                $this->staticCount++;
            }
        }
    }
}
