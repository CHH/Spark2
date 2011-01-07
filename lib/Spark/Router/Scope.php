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
    protected $options;
    
    function __construct($name, \Spark\Router $router)
    {
        $this->name    = $name;
        $this->router  = $router;
        $this->options = array("scope" => $name);
    }
    
    function match($route, $callback, Array $options = array())
    {
        $route = $this->name . '/' . ltrim($route, '/');
        $this->router->match($route, $callback, array_merge($this->options, $options));
        return $this;
    }
    
    function head($route, $callback, Array $options = array())
    {
        $route = $this->name . '/' . ltrim($route, '/');
        $this->router->head($route, $callback, array_merge($this->options, $options));
        return $this;
    }
    
    function get($route, $callback, Array $options = array())
    {
        $route = $this->name . '/' . ltrim($route, '/');
        $this->router->get($route, $callback, array_merge($this->options, $options));
        return $this;
    }
    
    function post($route, $callback, Array $options = array())
    {
        $route = $this->name . '/' . ltrim($route, '/');
        $this->router->post($route, $callback, array_merge($this->options, $options));
        return $this;
    }
    
    function put($route, $callback, Array $options = array())
    {
        $route = $this->name . '/' . ltrim($route, '/');
        $this->router->put($route, $callback, array_merge($this->options, $options));
        return $this;
    }
    
    function delete($route, $callback, Array $options = array())
    {
        $route = $this->name . '/' . ltrim($route, '/');
        $this->router->delete($route, $callback, array_merge($this->options, $options));
        return $this;
    }
}
