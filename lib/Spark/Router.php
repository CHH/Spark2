<?php
/**
 * Simple Router
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Util
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */
 
namespace Spark;

require_once('Router/RestRoute.php');
use Router\RestRoute as RestRoute;

class Router
{
    protected $routes = array();
    
    public function __construct(Array $options = array())
    {
        $this->setOptions($options);
    }
    
    public function setOptions(Array $options)
    {
        Options::setOptions($this, $options);
        return $this;
    }
    
    public function route(Controller\HttpRequest $request)
    {
        $matched = false;
        
        foreach (array_reverse($this->routes) as $key => $route) {
            $params = $route->match($request);
            
            if ($params) {
                $matched = true;
                break;
            }
        }
        
        if (!$matched) {
            throw new Controller\Exception("No Route matched");
        }
        
        foreach ($params as $param => $value) {
            $request->setParam($param, $value);
        }
        
        return $request;
    }
    
    public function map($routeSpec, $callback, Array $options = array())
    {
        $route = new RestRoute(null, $routeSpec, $callback, $options);
        $this->routes[] = $route;
        return $this;
    }
    
    public function head($routeSpec, $callback, Array $options = array())
    {
        $route = new RestRoute("HEAD", $routeSpec, $callback, $options);
        $this->routes[] = $route;
        return $this;
    }
    
    public function get($routeSpec, $callback, Array $options = array())
    {
        $route = new RestRoute("GET", $routeSpec, $callback, $options);
        $this->routes[] = $route;
        return $this;
    }
    
    public function post($routeSpec, $callback, Array $options = array())
    {
        $route = new RestRoute("POST", $routeSpec, $callback, $options);
        $this->routes[] = $route;
        return $this;
    }
    
    public function put($routeSpec, $callback, Array $options = array())
    {
        $route = new RestRoute("PUT", $routeSpec, $callback, $options);
        $this->routes[] = $route;
        return $this;
    }
    
    public function delete($routeSpec, $callback, Array $options = array())
    {
        $route = new RestRoute("DELETE", $routeSpec, $callback, $options);
        $this->routes[] = $route;
        return $this;
    }
}
