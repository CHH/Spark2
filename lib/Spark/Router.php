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

autoload('Spark\Router\Scope',      __DIR__ . '/Router/Scope.php');
autoload('Spark\Router\Exception',  __DIR__ . '/Router/Exception.php');
autoload('Spark\Router\NamedRoute', __DIR__ . '/Router/NamedRoute.php');

require_once('Router/Route.php');
require_once('Router/RestRoute.php');

use Spark\Router\RestRoute,
	Spark\Router\Exception,
    Spark\HttpRequest,
    Spark\Util\Options,
    SplStack,
    BadMethodCallException;

/**
 * TODO: Add support for named routes
 */
class Router
{
    protected $routes;
    protected $namedRoutes = array();
    
    function __construct()
    {
        $this->routes  = new SplStack;
    }
    
    function __invoke(HttpRequest $request)
    {
        return $this->route($request);
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
    
    function addRoute(Router\Route $route)
    {
        if ($route instanceof Router\NamedRoute and ($name = $route->getName())) {
            $this->namedRoutes[$name] = $route;
        }
        $this->routes->push($route);
        return $this;
    }
    
    function getRoute($name)
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route $route not registered");
        }
        return $this->namedRoutes[$name];
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
    
    function match($routeSpec, $callback = null)
    {
        if (is_string($routeSpec)) {
            $routeSpec = array($routeSpec => $callback);
        }
        return $this->addRoute($this->createRoute($routeSpec));
    }
    
    function __call($httpMethod, $arguments)
    {
        $httpMethod = strtoupper($httpMethod);
        
        if (!in_array($httpMethod, words("GET POST PUT DELETE HEAD OPTIONS"))) {
            throw new BadMethodCallException("Method $httpMethod() does not exist.");
        }
        
        $routeSpec  = isset($arguments[0]) ? $arguments[0] : null;
        $callback   = isset($arguments[1]) ? $arguments[1] : null;
        
        if (is_string($routeSpec)) {
            $routeSpec = array($routeSpec => $callback);
        }
        $routeSpec = array_merge($routeSpec, array("method" => $httpMethod));
        
        return $this->match($routeSpec);
    }
    
    protected function createRoute(Array $routeSpec)
    {
        return new RestRoute($routeSpec);
    }
}
