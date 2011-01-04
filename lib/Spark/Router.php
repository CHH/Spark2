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
 * @copyright  Copyright (c) 2011 Christoph Hochstrasser
 * @license    MIT License
 */
namespace Spark;

require_once('Util.php');

autoload('Spark\Router\Scope',     __DIR__ . '/Router/Scope.php');
autoload('Spark\Router\Exception', __DIR__ . '/Router/Exception.php');

require_once('Router/Route.php');
require_once('Router/RestRoute.php');

use Spark\Router\RestRoute,
	Spark\Router\Exception,
    Spark\HttpRequest,
    Spark\Util\Options,
    SplStack;

class Router
{
    protected $routes;
    
    function __construct(Array $options = array())
    {
        $this->routes  = new SplStack;
        $this->setOptions($options);
    }
    
    function setOptions(Array $options)
    {
        Options::setOptions($this, $options);
        return $this;
    }
    
    function route(HttpRequest $request)
    {
        $matched = false;
        
        foreach ($this->routes as $route) {
            try {
                $callback = $route->match($request);
            } catch (\Exception $e) {
                $callback = false;
            }
            if (false !== $callback) {
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            throw new Exception("No Route matched", 404);
        }
        $request->setCallback($callback);
        return $callback;
    }
    
    function scope($scope, $block)
    {
        if (!block_given(func_get_args())) {
            throw new \InvalidArgumentException("Second argument must be "
                . " a lambda expression");
        }
        $block(new Router\Scope($scope, $this));
        return $this;
    }
    
    function addRoute(Router\Route $route)
    {
        $this->routes->push($route);
        return $this;
    }
    
    function resource($resource, $callback, Array $options = array())
    {
        $resource = trim($resource, '/');
        $new      = $resource . '/new';
        
        $route    = $resource . '/:id';
        $edit     = $route    . '/edit';
        
        $this->get($new,  $callback, array_merge($options, array('action' => 'new')));
        $this->get($edit, $callback, array_merge($options, array('action' => 'edit')));
        
        $this->post($resource, $callback, $options);
        
        $this->get($route, $callback, array_merge($options, array("id" => null)));
        $this->put($route, $callback, $options);
        $this->delete($route, $callback, $options);
        
        return $this;
    }
    
    function map($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute(null, $routeSpec, $callback, $options));
    }
    
    function head($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("HEAD", $routeSpec, $callback, $options));
    }
    
    function get($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("GET", $routeSpec, $callback, $options));
    }
    
    function post($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("POST", $routeSpec, $callback, $options));
    }
    
    function put($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("PUT", $routeSpec, $callback, $options));
    }
    
    function delete($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("DELETE", $routeSpec, $callback, $options));
    }
}
