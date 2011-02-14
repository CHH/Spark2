<?php
/**
 * Simple Router
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Router
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */
 
/** @namespace */
namespace Spark;

require_once('Util.php');

autoload('Spark\Router\Exception',  __DIR__ . '/Router/Exception.php');
autoload('Spark\Router\Redirect',   __DIR__ . '/Router/Redirect.php');
autoload('Spark\Router\StringExpression', __DIR__ . '/Router/StringExpression.php');

require_once('Router/Route.php');
require_once('Router/RestRoute.php');

use Spark\Router\RestRoute,
	Spark\Router\Exception,
    Spark\Http\Request,
    Spark\Util,
    SplStack,
    SplObjectStorage,
    InvalidArgumentException;

class Router implements Router\Route
{
    /** @var SplStack */
    protected $routes;
    
    /** @var string Root of all routes of this router, used for scoping */
    protected $root = "/";
    
    /** @var SplObjectStorage */
    protected $callbacks;
    
    /** @var Router\Route */
    protected $matchedRoute;
    
    function __construct($root = null)
    {
        if ("/" !== substr($root, 0, 1)) {
            $root = "/" . $root;
        }
        $this->root   = $root;
        $this->routes = new SplStack;
        $this->callbacks = new SplObjectStorage;
    }

    /**
     * @alias route()
     */
    function __invoke(Request $request)
    {
        return $this->route($request);
    }
    
    /**
     * Routes the given request and returns the attached callback.
     *
     * @throws Spark\Router\Exception if no route matched
     * @param  Spark\HttpRequest $request
     * @return mixed Callback
     */
    function route(Request $request)
    {
        $matched = false;
        
        foreach ($this->routes as $route) {
            try {
                $matched = $route($request);
            } catch (\Exception $e) {
                $matched = false;
            }
            if (false !== $matched) {
                break;
            }
        }
        
        if (!$matched) {
            throw new Exception("No Route matched", 404);
        }
        
        $this->matchedRoute = $route;
        
        if ($route instanceof self) {
            $callback = $matched;
        } else {
            $callback = $this->getCallback($route);
        }
        
        $request->setCallback($callback);
        
        return $callback;
    }
    
    /**
     * Registers a custom route instance with the router.
     *
     * @param  Spark\Router\Route $route
     * @return Router
     */
    function addRoute(Router\Route $route)
    {
        $this->routes->push($route);
        return $this;
    }

    function getMatchedRoute()
    {
        return $this->matchedRoute;
    }    
    
    /**
     * Starts a routing scope
     *
     * @param  string $scope Prefix route
     * @param  object $block A Lambda expression, the routing scope gets passed
     *                       as first argument. This scope object behaves just like
     *                       a router, with the difference that all routes get prefixed
     *                       with the prefix specified in scope()
     * @return Router
     */
    function scope($root, $block)
    {
        if (!Util\block_given(func_get_args())) {
            throw new InvalidArgumentException("Second argument must be "
                . " a lambda expression");
        }
        $scope = new self(rtrim($this->root, "/") . "/" . ltrim($root, "/"));
        $block($scope);
        $this->routes->push($scope);
        return $this;
    }
    
    /**
     * Binds a callback to the route and registers the route with the router
     *
     * @param  mixed $routeSpec Route as string
     * @param  mixed $callback  Optional Callback, mandatory if route is given as string
     * @return Router
     */
    function match($routeSpec, $callback)
    {
        $route = $this->createRoute($routeSpec);
        $this->addCallback($route, $callback);
        $this->addRoute($route);
        return $route;
    }

    function get($routeSpec, $callback)
    {
        return $this->matchMethod("GET", $routeSpec, $callback);
    }
    
    function post($routeSpec, $callback)
    {
        return $this->matchMethod("POST", $routeSpec, $callback);
    }
    
    function put($routeSpec, $callback)
    {
        return $this->matchMethod("PUT", $routeSpec, $callback);
    }
    
    function delete($routeSpec, $callback)
    {
        return $this->matchMethod("DELETE", $routeSpec, $callback);
    }
    
    function head($routeSpec, $callback)
    {
        return $this->matchMethod("HEAD", $routeSpec, $callback);
    }

    function options($routeSpec, $callback)
    {
        return $this->matchMethod("OPTIONS", $routeSpec, $callback);
    }
    
    /**
     * Handle binding of routes to HTTP Methods such as GET, POST, PUT,...
     *
     * HTTP Method names can be directly called as methods on the router
     *
     * @param  string $httpMethod
     * @param  mixed  $routeSpec
     * @param  mixed  $callback
     * @return Router
     */
    protected function matchMethod($httpMethod, $routeSpec, $callback)
    {
        $httpMethod = strtoupper($httpMethod);

        if (!in_array($httpMethod, Util\words("GET POST PUT DELETE HEAD OPTIONS"))) {
            throw new InvalidArgumentException("Undefined HTTP Method $httpMethod");
        }
        
        return $this->match($routeSpec, $callback)->method($httpMethod);
    }
    
    /**
     * Creates an instance of the standard route
     *
     * @param  Array $routeSpec
     * @return Spark\Router\RestRoute
     */
    protected function createRoute($route)
    {
        $route = new RestRoute(rtrim($this->root, "/") . "/" . ltrim($route, "/"));
        $route->meta("scope", trim($this->root, "/"));
        return $route;
    }
    
    protected function addCallback(Router\Route $route, $callback)
    {
        $this->callbacks->attach($route, $callback);
        return $this;
    }
    
    protected function getCallback(Router\Route $route)
    {
        return $this->callbacks[$route];
    }
}
