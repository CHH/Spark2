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

autoload("Spark\Exception", __DIR__ . "/Exception.php");

require_once("Dispatcher.php");
require_once("Router.php");

use Spark\Http\Request, 
    Spark\Http\Response,
    Spark\Http\NotFoundException,
    Spark\Error,
    Spark\Dispatcher,
    Spark\Util,
    Spark\Util\FilterChain,
    SplStack;

class App
{
    /** @var Router */
    protected $router;
    
    /** @var Dispatcher */
    protected $dispatcher;
    
    protected $errorHandlers;
    
	/** @var array */
	protected $options = array();
    
    /** @var FilterChain holds all middleware */
    protected $stack;
    
    protected $response;
    
	final function __construct()
	{
        $this->stack = new FilterChain;
        $this->errorHandlers = new SplStack;
        
        // Add default middleware
        $this->stack
             ->append($this->getRouter())
             ->append($this->getDispatcher());
        
        $this->init();
    }
    
    function __invoke(Request $request)
    {
	    $response = $this->getResponse();
		
		ob_start();
		try {
			$returnValues = $this->stack->filterUntil(
			    array($request), array($request, "isDispatched")
			);
			
			// Aggregate returned responses
			foreach ($returnValues as $return) {
	            if (!$return instanceof ResponseInterface) {
	                continue;
	            }
                $response->addHeaders($return->getHeaders());
                $response->appendContent($return->getContent());
                $response->setStatus($return->getStatus());
	        }
	        
	        if (!$request->isDispatched() or 404 === $response->getStatus()) {
	            throw new NotFoundException("The requested URL was not found");
	        }
	    
		} catch (\Exception $e) {
		    // Let the Exception bubble up if no error handlers are registered
		    if (0 === count($this->errorHandlers)) {
		        throw $e;
		    }
		
			$error = new Error("An Exception occured: {$e->getMessage()}", $request, $e);
			
			foreach ($this->errorHandlers as $handler) {
                call_user_func($handler, $error);
            }
		}
	    
	    $response->appendContent(ob_get_clean());
	    
	    if ($this->get("return_response")) {
	        return $response;
	    }
	    $response->send();
	    return $this;
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
    function before($filter)
    {
        $this->getDispatcher()->before($filter);
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
	    $this->getDispatcher()->after($filter);
	    return $this;
	}

    /**
     * Registers an error handler
     */
    function error($callback) {
        if (!is_callable($handler)) {
	        throw new InvalidArgumentException("You must supply a callback as error handler");
	    }
		$this->errorHandlers->push($handler);
		return $this;
    }

    /**
     * Registers an handler on the error code 404
     *
     * @param  callback $callback
     * @return App
     */
    function notFound($callback) {
        $callback = function($error) use ($callback) {
            $e = $error->getException();
            
            if (404 === $e->getCode()) {
                call_user_func($callback, $request, $response);
            }
            else return;
        };
        $this->error($callback);
        return $this;
    }
    
    /**
     * Returns a Dispatcher instance
     *
     * @return Dispatcher
     */
	function getDispatcher()
	{
	    if (null === $this->dispatcher) {
	        $this->dispatcher = new Dispatcher;
	    }
	    return $this->dispatcher;
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
    
    function getResponse()
    {
        if (null === $this->response) {
            $this->response = new Response;
        }
        return $this->response;
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
