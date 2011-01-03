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
 * @copyright  Copyright (c) 2011 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

require_once('Util.php');
require_once("HttpRequest.php");
require_once("HttpResponse.php");
require_once("Router.php");

use SplStack,
    Spark\HttpRequest, 
    Spark\HttpResponse,
    Spark\Util\Options;

class App
{
    /** @var Spark\Router */
	public $routes;
    
	/** @var SplStack */
	protected $postDispatch;

    /** @var SplStack */
    protected $preDispatch;
	
	/** @var array */
	protected $metadata = array();
	
	function __construct()
	{
        $this->routes = new Router;
        
        $this->preDispatch  = new SplStack;
        $this->postDispatch = new SplStack;	
    }
	
	/**
	 * Sets metadata which can be retrieved by modules extending the app 
	 *
	 * @param  mixed $spec  Either array of key value pairs or single key
	 * @param  mixed $value Optional value, if key is a scalar
	 * @return App
	 */
	function setMetadata($spec, $value = null)
	{
	    if (is_array($spec)) {
	        foreach ($spec as $option => $value) {
	            $this->setMetadata($option, $value);
	        }
	        return $this;
	    }
	    $this->metadata[$spec] = $value;
	    return $this;
	}
	
	function getMetadata($key = null) 
	{
	    if (null === $key) {
	        return $this->metadata;
	    }
	    if (!isset($this->metadata[$spec])) {
	        return null;
	    }
	    return $this->metadata[$spec];
	}
	
	/**
	 * Routes the request, dispatch the callback, captures all output and sends
	 * back the response
	 *
	 * @param  HttpRequest  $request
	 * @param  HttpResponse $response
	 * @return void
	 */
	function __invoke(HttpRequest $request, HttpResponse $response)
	{
	    ob_start();
	    
	    try {
	        $this->routes->route($request);
            
            foreach ($this->preDispatch as $filter) {
                $filter($request, $response);
            }
            
            $callback = $this->validateCallback($request->getMetadata("callback"));
	        $callback($request, $response);
	        
		} catch (\Exception $e) {
		    $response->addException($e);
		}
		
		$response->append(ob_get_clean());
		
		foreach ($this->postDispatch as $filter) {
		    $filter($request, $response);
		}
		
		$response->send();
	}
    
    /**
     * Attaches a filter to the filters run before dispatching
     *
     * @param  object $filter Callable object (Closure or Object implementing __invoke)
     * @return App
     */
    function preDispatch($filter)
    {
        $this->preDispatch->push($filter);
        return $this;
    }
	
	/**
     * Attaches a filter to the filters run after dispatching
     *
     * @param  object $filter Callable object (Closure or Object implementing __invoke)
     * @return App
     */
	function postDispatch($filter)
	{
	    $this->postDispatch->push($filter);
	    return $this;
	}
    
    /**
     * Validates if the callback is callable and wraps array style callbacks
     * in a closure to unify calling
     *
     * @param  mixed $callback
     * @return Closure
     */
	protected function validateCallback($callback)
	{
        if (!is_callable($callback)) {
            throw new \RuntimeException("The callback is not valid");
        }
        
        if (is_array($callback) or is_string($callback)) {
            $callback = function($request, $response) use ($callback) {
                return call_user_func($callback, $request, $response);
            };
        }
        return $callback;
	}
}
