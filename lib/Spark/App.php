<?php
/**
 * Application base class, facade for controller and router
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Util
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

use Spark\Http\Request, 
    Spark\Http\Response,
    Spark\Http\NotFoundException,
    Spark\Error,
    Spark\Dispatcher,
    Spark\Util,
    Spark\Util\FilterChain,
    Symfony\Component\EventDispatcher\Event,
    Symfony\Component\EventDispatcher\EventDispatcher,
    SplStack;

class App
{
    /** @var Router */
    protected $router;
    
    /** @var Dispatcher */
    protected $dispatcher;
    
    protected $eventDispatcher;
    
	/** @var array */
	protected $options = array();
    
    /** @var Response */
    protected $response;
    
	final function __construct()
	{
        $this->eventDispatcher = new EventDispatcher;
        
        // Add default middleware
        $this->eventDispatcher->connect("spark.request", $this->getRouter());
        $this->eventDispatcher->connect("spark.request", $this->getDispatcher());
        
        $this->init();
    }
    
    /**
     * Template Method which can be used to initialize Subclasses
     */
    function init()
    {}
    
    /**
     * Dispatches the request and sends the Response
     *
     * @param  Request $request 
     * @return App|Response Returns the response if "return_response" is TRUE
     */
    function __invoke(Request $request)
    {
	    $response = $this->getResponse();
		$eventDispatcher = $this->eventDispatcher;
		
		ob_start();
		try {
		    $onRequest = new Event($this, "spark.request", array("request" => $request));
		    $response = $eventDispatcher->notifyUntil($onRequest);
	        
	        if (404 === $response->getStatusCode()) {
	            throw new NotFoundException("The requested URL was not found");
	        }
		} catch (\Exception $e) {
			$error = new Event($this, "spark.error", array(
			    "request" => $request, 
			    "exception" => $e
            ));
			
			$response = $eventDispatcher->notifyUntil($error);
			
			if (!$error->isProcessed()) {
			    throw $e;
			}
		}
	    
	    $response->write(ob_get_clean());

        $shutdown = new Event($this, "spark.shutdown", array(
            "request" => $request,
            "response" => $response
        ));
        $eventDispatcher->notify($shutdown);
	    
	    return $response;
    }
    
    /**
     * Sets an option
     * 
     * @param  string|array $spec Either list of key-values or name of the key
     * @param  mixed $value
     * @return App
     */
	function set($spec, $value = null)
	{
	    if (is_array($spec)) {
	        foreach ($spec as $option => $value) {
	            $this->options[$option] = $value;
	        }
	        return $this;
	    }
	    $this->options[$spec] = $value;
	    return $this;
	}
    
    /**
     * Get an option
     *
     * @param  mixed $spec Returns the value of the option or all options if NULL
     * @return mixed
     */
    function get($spec = null)
    {
        if (null === $spec) {
            return $this->options;
        }
        return !empty($this->options[$spec]) ? $this->options[$spec] : null;
    }
    
    /**
     * Provides access to routes
     * 
     * @param  callback $block Either a callback or NULL, if NULL returns a router instance
     * @return App|Router If $block is a callback, then it returns the App, if the block
     *                    is NULL, then it returns a Router instance
     */
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
    function before($handler)
    {
        $this->eventDispatcher->connect("spark.before_dispatch", $handler);
        return $this;
    }

	/**
     * Attaches a filter to the filters run after dispatching
     *
     * @param  object $filter Callable object (Closure or Object implementing __invoke)
     * @return App
     */
	function after($handler)
	{
	    $this->eventDispatcher->connect("spark.after_dispatch", $handler);
	    return $this;
	}

    /**
     * Registers an error handler
     */
    function error($handler) {
		$this->eventDispatcher->connect("spark.error", $handler);
		return $this;
    }
    
    function shutdown($handler)
    {
        $this->eventDispatcher->connect("spark.shutdown", $handler);
        return $this;
    }
    
    /**
     * Registers an handler on the error code 404
     *
     * @param  callback $callback
     * @return App
     */
    function notFound($callback) {
        $callback = function($event) use ($callback) {
            $e = $event->get("exception");
            
            if (404 === $e->getCode()) {
                return call_user_func($callback, $error);
            }
            else return;
        };
        $this->error($callback);
        return $this;
    }
    
    /**
     * Returns a Router instance
     *
     * @return Router
     */
	function getRouter()
	{
	    if (null === $this->router) {
	        $this->router = new Router;
	    }
	    return $this->router;
	}
    
    function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }
    
    /**
     * @return Response
     */
    function getResponse()
    {
        if (null === $this->response) {
            $this->response = new Response;
        }
        return $this->response;
    }
    
    /**
     * Returns a Dispatcher instance
     *
     * @return Dispatcher
     */
	protected function getDispatcher()
	{
	    if (null === $this->dispatcher) {
	        $this->dispatcher = new Dispatcher;
	    }
	    return $this->dispatcher;
	}
}
