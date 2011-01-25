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
    Spark\Util;

function App(App $app = null)
{
    static $instance;
    
    if (null !== $app) {
        $instance = $app;
    }
    if (null === $instance) {
        $instance = new App;
    }
    return $instance;
}

class App
{
    /** @var Spark\Router */
	protected $router;
    
	/** @var SplQueue */
	protected $postDispatch;

    /** @var SplQueue */
    protected $preDispatch;

    /** @var array */
    protected $onError = array(0 => array());
	
	/** @var array */
	protected $options = array();
    
    protected $configurators;
    
	final function __construct()
	{
	    $this->configurators = new SplQueue;
        $this->preDispatch   = new SplQueue;
        $this->postDispatch  = new SplQueue;

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
    
    function configure($filter)
    {
        $this->configurators->enqueue($filter);
        return $this;
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
        $this->preDispatch->enqueue($filter);
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
	    $this->postDispatch->enqueue($filter);
	    return $this;
	}

    /**
     * Registers an error handler
     */
    function error($class, $callback = null) {
        if (null === $callback) {
            $callback = $class;
            $class    = 0;
        }
        if (is_array($callback) or !empty($callback)) {
            $callback = function($request, $response) use ($callback) {
                return call_user_func($callback, $request, $response);
            };
        }
        $this->onError[$class][] = $callback;
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
	    
	    foreach ($this->configurators as $filter) {
	        $filter($this);
	    }
	    
	    try {
	        $this->router->route($request);
            
            foreach ($this->preDispatch as $filter) {
                $filter($request, $response);
            }
            
            $callback = $this->validateCallback($request->getCallback());
	        $callback($request, $response);
	        
		} catch (\Exception $e) {
		    $response->setException($e);

            if (isset($this->onError[$class = get_class($e)])) {
                $errorHandlers = $this->onError[$class];
            } else {
                $errorHandlers = $this->onError[0];
            }
		    
		    foreach ($errorHandlers as $handler) {
                $handler($request, $response);
		    }
		}
		
		foreach ($this->postDispatch as $filter) {
		    $filter($request, $response);
		}
		
		$response->append(ob_get_clean())->send();
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
