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

class App implements \SparkCore\Framework
{
    /** @var Spark\Router */
	protected $router;
    
    /** @var Spark\Dispatcher */
    protected $dispatcher;
    
	/** @var SplQueue */
	protected $postDispatch;

    /** @var SplQueue */
    protected $preDispatch;

	/** @var array */
	protected $options = array();

    protected $errorHandlers;
    
	final function __construct()
	{
        $this->preDispatch   = new FilterChain;
        $this->postDispatch  = new FilterChain;
        $this->init();
    }

    // TODO: Fix Error handlers
    function setUp(\SparkCore $core)
    {
        $core->append(
            $this->preDispatch, 
            $this->getRouter(),
            $this->getDispatcher(),
            $this->postDispatch
        );

        $core->setErrorHandlers($this->errorHandlers);
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
        $this->preDispatch->append($filter);
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
	    $this->postDispatch->append($filter);
	    return $this;
	}

    /**
     * Registers an error handler
     */
    function error($callback) {
        $this->errorHandlers->append($callback);
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
        $this->errorHandlers->append($callback);
        return $this;
    }
    
	function getDispatcher()
	{
	    if (null === $this->dispatcher) {
	        $this->dispatcher = new Dispatcher;
	    }
	    return $this->dispatcher;
	}

	function getRouter()
	{
	    if (null === $this->router) {
            $this->router = new Router;
        }
	    return $this->router;
	}
}
