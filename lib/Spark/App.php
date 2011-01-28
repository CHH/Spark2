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

require_once("Util.php");

autoload("Spark\Exception", __DIR__ . "/Exception.php");

require_once("HttpRequest.php");
require_once("HttpResponse.php");
require_once("Router.php");
require_once('Controller.php');

use SplQueue,
    Spark\HttpRequest, 
    Spark\HttpResponse,
    Spark\Util,
    Spark\Util\HttpFilters;

class App
{
    /** @var Spark\Router */
	protected $router;
    
	/** @var SplQueue */
	protected $postDispatch;

    /** @var SplQueue */
    protected $preDispatch;

    /** @var array Error handlers */
    protected $onError;
	
	/** @var array */
	protected $options = array();
    
	final function __construct()
	{
        $this->preDispatch  = new HttpFilters;
        $this->postDispatch = new HttpFilters;
        $this->onError      = new HttpFilters;
        
        $this->before($this->route());
        $this->init();
    }
    
    function init()
    {}

    /**
     * @alias setOption()
     */
	function set($spec, $value = null)
	{
	    return $this->setOption($spec, $value);
	}
    
    function get($spec = null)
    {
        if (null === $spec) {
            return $this->getOptions();
        }
        return $this->getOption($spec);
    }
    
	/**
	 * Sets metadata which can be retrieved by modules extending the app 
	 *
	 * @param  mixed $spec  Either array of key value pairs or single key
	 * @param  mixed $value Optional value, if key is a scalar
	 * @return App
	 */
    function setOption($spec, $value = null)
    {
        if (is_array($spec)) {
	        foreach ($spec as $option => $value) {
	            $this->set($option, $value);
	        }
	        return $this;
	    }
	    $this->options[$spec] = $value;
	    return $this;
    }
	
	function getOption($spec = null) 
	{
	    if (!isset($this->options[$spec])) {
	        return null;
	    }
	    return $this->options[$spec];
	}
    
    function getOptions()
    {
        return $this->options;
    }

    function route($block = null)
    {   
        if (null === $this->router) {
            $this->router = new Router;
        }
        if (null === $block) {
            return $this->router;
        }
        if (is_callable($block)) {
            call_user_func($block, $this->router);
            return $this;
        }
    }
    
    /**
     * Attaches a filter to the filters run before dispatching
     *
     * @param  object $filter Callable object (Closure or Object implementing __invoke)
     * @return App
     */
    function before($filter)
    {
        $this->preDispatch->queue($filter);
        return $this;
    }

	/**
     * Attaches a filter to the filters run after dispatching
     *
     * @param  object $filter Callable object (Closure or Object implementing __invoke)
     * @return App
     */
	function after($filter)
	{
	    $this->postDispatch->queue($filter);
	    return $this;
	}

    /**
     * Registers an error handler
     */
    function error($callback) {
        $this->onError->queue($callback);
        return $this;
    }

    /**
     * Registers an handler on the error code 404
     */
    function notFound($callback) {
        $callback = function($request, $response) use ($callback) {
            $e = $response->getException();

            if (404 === $e->getCode()) call_user_func($callback, $request, $response);
            else return;
        };
        $this->error($callback);
        return $this;
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
	        $this->preDispatch->filter($request, $response);
            
            $callback = $this->validateCallback($request->getCallback());
	        $callback($request, $response);
	        
		} catch (\Exception $e) {
		    $response->setException($e);
            $this->onError->filter($request, $response);
		}
		
		// Attach all stdout output from callbacks
		$response->append(ob_get_clean());
		
		// Attach all stdout output from post dispatch filters
		ob_start();
		$this->postDispatch->filter($request, $response);
		$response->append(ob_get_clean());
		
		$response->send();
		return $this;
	}
    
    /**
     * Validates if the callback is callable and wraps array style callbacks
     * in a closure to allow closure-style calling
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
