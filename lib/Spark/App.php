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
    Spark\Dispatcher,
    Spark\Util,
    Spark\Util\FilterChain,
    Spark\View,
    SplStack;

class App
{
    /** @var Router */
    protected $router;
    
    /** @var Dispatcher */
    protected $dispatcher;
    
	/** @var array */
	protected $options = array();

    protected $requestHandlers;

    protected $shutdownHandlers;

    protected $errorHandlers;
    
    /** @var Response */
    protected $response;
    
	final function __construct()
	{
        $this->requestHandlers  = new FilterChain;
        $this->errorHandlers    = new FilterChain;
        $this->startupHandlers  = new FilterChain;
        $this->shutdownHandlers = new FilterChain;
        
        // Add default middleware
        $this->requestHandlers
             ->add($this->getRouter())
             ->add($this->getDispatcher());
        
        $this->init();
    }
    
    /**
     * Template Method which can be used to initialize Subclasses
     */
    function init()
    {}
    
    function run()
    {
        $request  = Request::createFromGlobals();
        $response = $this($request);
        $response->send();
    }
    
    /**
     * Dispatches the request and sends the Response
     *
     * @param  Request $request 
     * @return App|Response Returns the response if "return_response" is TRUE
     */
    function __invoke(Request $request)
    {
	    $response = $this->getResponse();
		
		try {
            $this->startupHandlers->filter(array($request));
		
		    $response->merge($this->requestHandlers->filter(array($request)));
	        
	        if ($response->isNotFound()) {
	            throw new NotFoundException("The requested URL was not found");
	        }
		} catch (\Exception $e) {
            if ($this->errorHandlers->isEmpty()) {
                throw $e;
            }
		
			$error = new \StdClass;
			$error->exception = $e;
			$error->request = $request;

			ob_start();
			$response->merge($this->errorHandlers->filter(array($error)));
			$response->write(ob_get_clean());
		}

	    ob_start();
        $this->shutdownHandlers->filter(array($request, $response));
        $response->write(ob_get_clean());
	    
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
        $this->getDispatcher()->before($handler);
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
	    $this->getDispatcher()->after($handler);
	    return $this;
	}

    /**
     * Registers an error handler
     */
    function error($handler) {
		$this->errorHandlers->add($handler);
		return $this;
    }

    function startup($handler)
    {
        $this->startupHandlers->add($handler);
        return $this;
    }
    
    function shutdown($handler)
    {
        $this->shutdownHandlers->add($handler);
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
            $e = $error->exception;
            
            if (404 === $e->getCode()) {
                return call_user_func($callback, $error);
            }
            else return;
        };
        $this->error($callback);
        return $this;
    }

    function render($template, $view = null)
    {
        return View::render($template, $view);
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
