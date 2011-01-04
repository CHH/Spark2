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
    
    public function __construct($name, \Spark\Router $router)
    {
        $this->name   = $name;
        $this->router = $router;
    }
    
    public function resource($resource, $callback, Array $options = array())
    {
        $resource = $this->name . '/' . ltrim($resource, '/');
        $this->router->resource($resource, $callback, $options);
    }
    
    public function head($resource, $callback, Array $options = array())
    {
        $resource = $this->name . '/' . ltrim($resource, '/');
        $this->router->head($resource, $callback, $options);
        return $this;
    }
    
    public function get($resource, $callback, Array $options = array())
    {
        $resource = $this->name . '/' . ltrim($resource, '/');
        $this->router->get($resource, $callback, $options);
        return $this;
    }
    
    public function post($resource, $callback, Array $options = array())
    {
        $resource = $this->name . '/' . ltrim($resource, '/');
        $this->router->post($resource, $callback, $options);
        return $this;
    }
    
    public function put($resource, $callback, Array $options = array())
    {
        $resource = $this->name . '/' . ltrim($resource, '/');
        $this->router->put($resource, $callback, $options);
        return $this;
    }
    
    public function delete($resource, $callback, Array $options = array())
    {
        $resource = $this->name . '/' . ltrim($resource, '/');
        $this->router->delete($resource, $callback, $options);
        return $this;
    }
}
