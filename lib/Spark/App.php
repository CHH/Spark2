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

require_once("Dispatcher.php");
require_once("Router.php");
require_once('Controller.php');

use SparkCore\Http\Request, 
    SparkCore\Http\Response,
    SparkCore\FilterChain,
    Spark\Dispatcher,
    Spark\Util;

class App
{
	/** @var SplQueue */
	protected $before;

    /** @var SplQueue */
    protected $after;

	/** @var array */
	protected $options = array();
    
	final function __construct()
	{
        $this->before = new FilterChain;
        $this->after  = new FilterChain;
        $this->init();
    }
    
    function __invoke(Request $request)
    {
        $router = $this->getRouter();
        $router($request);

        $this->before->filter($request);
        
        $dispatcher = $this->getDispatcher();
        $response = $dispatcher($request);
        
        $this->after->filter($request);
        return $response;
    }
    
    function init()
    {}

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

    function route($block = null)
    {
        $router = $this->getRouter();
        if (null === $block) {
            return $router;
        }
        if (is_callable($block)) {
            call_user_func($block, $router);
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
        $this->before->append($filter);
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
	    $this->after->append($filter);
	    return $this;
	}

    /**
     * Registers an error handler
     */
    function error($callback) {
        SparkCore()->error($callback);
        return $this;
    }

    /**
     * Registers an handler on the error code 404
     */
    function notFound($callback) {
        $callback = function($request, $response) use ($callback) {
            $e = $response->getException();
            
            if (404 === $e->getCode() or 404 === $response->statusCode()) {
                call_user_func($callback, $request, $response);
            }
            else return;
        };
        $this->error($callback);
        return $this;
    }
    
	function getDispatcher()
	{
	    return Dispatcher();
	}

	function getRouter()
	{
	    return Router();
	}
    
	/**
	 * Sets metadata which can be retrieved by modules extending the app 
	 *
	 * @param  mixed $spec  Either array of key value pairs or single key
	 * @param  mixed $value Optional value, if key is a scalar
	 * @return App
	 */
    protected function setOption($spec, $value = null)
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
	
	protected function getOption($spec = null) 
	{
	    if (!isset($this->options[$spec])) {
	        return null;
	    }
	    return $this->options[$spec];
	}
    
    protected function getOptions()
    {
        return $this->options;
    }
}
