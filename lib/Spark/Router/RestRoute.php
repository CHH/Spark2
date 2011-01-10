<?php
/**
 * Standard Route
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Router
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2011 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Router;

class RestRoute implements NamedRoute
{
    protected $name;    
    
    protected $method;
    protected $route;
    protected $parsedRoute;
    protected $defaults;
    protected $callback;    
    
    protected $urlDelimiter = "/";
    protected $urlParam     = ":";
    
    protected $params = array();
    protected $parts  = array();
    protected $staticCount = 0;
    
    protected $wildcardData = array();
    
    /**
     * Constructor
     *
     * The constructor takes an array of options as sole argument. The first element
     * in the array is treated as the route. If this element has the index [0], then
     * the value is treated as route. If the index of the first element is a string, 
     * then the key is treated as route and the value as callback.
     * 
     * All other elements in the array are treated as options. These include:
     *   - "to", callback for this route
     *   - "as", route name
     *   - "method", which HTTP method this route should match, if not given or NULL
     *     then the method is not considered
     *
     * All other elements than these defined options are stored and set as metadata
     * on the request if the route gets matched
     *
     * @param  Array $routeSpec
     * @return RestRoute
     */
    function __construct(Array $routeSpec)
    {
        $options = array_slice($routeSpec, 1) ?: array();
        $route   = array_slice($routeSpec, 0, 1);
        
        // If first element of array is a $route => $callback pair
        if (is_string(key($route))) {
            $callback = current($route);
            $route    = key($route);
        } else {
            $route = current($route);
        }
        
        $callback = array_delete_key("to", $options) ?: $callback;
        $name     = array_delete_key("as", $options);
        
        if ($method = array_delete_key("method", $options)) {
            $this->method = strtoupper($method);
        }
        
        $this->route     = trim($route, $this->urlDelimiter);
        $this->callback  = $callback;
        $this->defaults  = $options;
        $this->name      = $name;
        
        $this->parseRoute();
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

        foreach ($params as $key => $value) {
            $request->setMetadata($key, $value);
        }
        
        return $this->callback;
    }
    
    function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    function getName()
    {
        return $this->name;
    }
    
    function assemble(Array $params)
    {
        $url = $this->route;
        
        foreach ($params as $key => $value) {
            $url = str_replace(":$key", $value, $url);
        }
        
        return "/" . $url;
    }
    
    protected function parseRoute()
    {
        if ($this->params and $this->parts) {
            return;
        }
        $parts = explode($this->urlDelimiter, $this->route);
        
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
