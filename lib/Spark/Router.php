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

autoload('Spark\Router\Scope', __DIR__ . '/Router/Scope.php');

require_once('Router/Route.php');
require_once('Router/RestRoute.php');

use Spark\Router\RestRoute as RestRoute;

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
    
    public function scope($scope, $block)
    {
        if (!block_given(func_get_args())) {
            throw new \InvalidArgumentException("Second argument must be "
                . " a lambda expression");
        }
        $block(new Router\Scope($scope, $this));
        return $this;
    }
    
    public function addRoute(Router\Route $route)
    {
        $this->routes[] = $route;
        return $this;
    }
    
    public function resource($resource, $callback, Array $options = array())
    {
        $resource = trim($resource, '/');
        $new      = $resource . '/new';
        $route    = $resource . '/:id';
        $edit     = $route    . '/edit';
        
        $this->get($new,  $callback, array_merge($options, array('action' => 'new')));
        $this->get($edit, $callback, array_merge($options, array('action' => 'edit')));
        
        $this->post($resource, $callback, $options);
        
        $this->get    ($route, $callback, $options);
        $this->put    ($route, $callback, $options);
        $this->delete ($route, $callback, $options);
        
        return $this;
    }
    
    public function map($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute(null, $routeSpec, $callback, $options));
    }
    
    public function head($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("HEAD", $routeSpec, $callback, $options));
    }
    
    public function get($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("GET", $routeSpec, $callback, $options));
    }
    
    public function post($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("POST", $routeSpec, $callback, $options));
    }
    
    public function put($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("PUT", $routeSpec, $callback, $options));
    }
    
    public function delete($routeSpec, $callback, Array $options = array())
    {
        return $this->addRoute(new RestRoute("DELETE", $routeSpec, $callback, $options));
    }
}
