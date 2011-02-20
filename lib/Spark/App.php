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
    SplStack;

class App
{
    /** @var Router */
    protected $router;
    
    /** @var Dispatcher */
    protected $dispatcher;
    
    /** @var SplStack */
    protected $errorHandlers;
    
	/** @var array */
	protected $options = array();
    
    /** @var FilterChain holds all middleware */
    protected $stack;
    
    /** @var Spark\View\Renderer */
    protected $viewRenderer;
    
    /** @var Response */
    protected $response;
    
	final function __construct()
	{
        $this->stack = new FilterChain;
        $this->errorHandlers = new FilterChain;
        
        // Add default middleware
        $this->stack
             ->append($this->getRouter())
             ->append($this->getDispatcher());
        
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
		
		ob_start();
		try {
			$responses = $this->stack->filterUntil(
			    array($request), array($request, "isDispatched")
			);
			
			// Aggregate returned responses
			foreach ($responses as $r) {
	            if (!$r instanceof Response) {
	                continue;
	            }
                $response->addHeaders($r->getHeaders());
                $response->appendContent($r->getContent());
                $response->setStatus($r->getStatus());
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
			$responses = $this->errorHandlers->filter(array($error));
			
			// Aggregate returned responses
			foreach ($responses as $r) {
	            if (!$r instanceof Response) {
	                continue;
	            }
                $response->addHeaders($r->getHeaders());
                $response->appendContent($r->getContent());
                $response->setStatus($r->getStatus());
	        }
		}
	    
	    $response->appendContent(ob_get_clean());
	    
	    if ($this->get("return_response")) {
	        return $response;
	    }
	    $response->send();
	    return $this;
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
    
    function render($template = null, $view = null)
    {
        $viewRenderer = $this->getViewRenderer();
        
        $response = $viewRenderer($template, $view);
        return $response;
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
		$this->errorHandlers->append($callback);
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
    
    function setViewRenderer(\Spark\View\Renderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;
        return $this;
    }
    
    function getViewRenderer()
    {
        if (null === $this->viewRenderer) {
            $this->viewRenderer = new \Spark\View\StandardRenderer;
        }
        return $this->viewRenderer;
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
