<?php
/**
 * Application base class, facade for controller and router
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_App
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

autoload('Spark\Event', __DIR__ . "/Event.php");

require_once("Controller.php");
require_once("Router.php");

class App
{
	public $routes;
	
	function __construct()
	{
		$router = new Router();
		$router->addFilter($this->getRouterFilter());
		
		$this->routes = $router;
	}
	
	function __invoke(
		Controller\HttpRequest  $request, 
		Controller\HttpResponse $response
	)
	{
	    try {
		    $callback = $this->routes->route($request);
		    $callback($request, $response);
		} catch (Exception $e) {
		    // handle error
		}
		
		$response->send();
	}
	
    function setResolver(Controller\Resolver $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }
	
	function getResolver()
	{
	    if (null === $this->resolver) {
	        $this->resolver = new Controller\StandardResolver;
	    }
	    return $this->resolver;
	}
	
	protected function getRouterFilter()
	{
	    $resolver = $this->getResolver();
	    
	    return function($request) use ($resolver) {
	        $callback = $request->getUserParam("__callback");
	        
	        $controller = array_delete_key("controller", $callback) 
	            ?: $request->getParam("controller");
	        
	        $module = array_delete_key("module", $callback)
	            ?: $request->getParam("module");
	        
	        $callback = $resolver->getControllerByName($controller, $module);
	        
	        if (false === $callback) {
	            throw new Controller\Exception(
	                "$module::$controller is not a valid Controller"
                );
	        }
	        $request->setParam("__callback", $callback);
	    };
	}
}
