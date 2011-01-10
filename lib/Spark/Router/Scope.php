<?php
/**
 * Route scope, gets passed as argument to block of Spark\Router::scope()
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @see        Spark\Router::scope()
 * @category   Spark
 * @package    Spark_Router
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2011 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Router;

class Scope
{
    protected $name;
    protected $router;
    
    function __construct($name, \Spark\Router $router)
    {
        $this->name    = $name;
        $this->router  = $router;
    }
    
    function __call($method, $arguments)
    {
        $routeSpec = isset($arguments[0]) ? $arguments[0] : false;
        $callback  = isset($arguments[1]) ? $arguments[1] : null;
        
        if (!$routeSpec) {
            throw new \InvalidArgumentException("No Route spec given");
        }
        
        if (is_array($routeSpec)) {
            reset($routeSpec);
            if (is_string($scopedRoute = key($routeSpec))) {
                $originalCallback = array_shift($routeSpec);
                
                $routeSpec = array_merge(
                    array($this->name . "/" . ltrim($scopedRoute, "/") => $originalCallback),
                    $routeSpec
                );
                
            } else if (is_string($scopedRoute = array_shift($routeSpec))) {
                array_unshift($routeSpec, $this->name . "/" . ltrim($scopedRoute, "/"));
            }
        } else if (is_string($routeSpec)) {
            $routeSpec = array($this->name . "/" . ltrim($routeSpec, "/") => $callback);
        }
        
        // Add scope metadata to route
        $routeSpec = array_merge($routeSpec, array("scope" => $this->name));
        
        $this->router->{$method}($routeSpec, $callback);
        return $this;
    }
}
