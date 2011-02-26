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
    Spark\Util\FilterChain,
    Spark\View;

class App
{
    /** @var \Spark\Util\ExtensionManager */
    protected $extensions;

    /** @var Router */
    protected $router;
    
    /** @var Dispatcher */
    protected $dispatcher;

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

        $this->extensions = new \Spark\Util\ExtensionManager;

        $this->register("\Spark\Extension\Configuration");
        $this->register("\Spark\Extension\ViewRenderer");
        
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

        $returnValues = array();
		
		try {
            $this->startupHandlers->filter(array($request, $response));
		
		    foreach ($this->requestHandlers->filter(array($request)) as $return) {
                $returnValues[] = $return;
		    }
	        
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
			$error->response = $response;

			ob_start();
			foreach ($this->errorHandlers->filter(array($error)) as $return) {
                $returnValues[] = $return;
			}
			
			$response->write(ob_get_clean());
		}

	    ob_start();
        $this->shutdownHandlers->filter(array($request, $response));
        $response->write(ob_get_clean());

        foreach ($returnValues as $return) {
            if (is_string($return)) {
                $response->write($return);
            }
            if ($return instanceof Response) {
                $response->headers->add($return->headers->all());
                $response->write($return->getContent());
                $response->setStatusCode($return->getStatusCode());
            }
        }
	    
	    return $response;
    }
    
    /**
     * Call extensions
     */
    function __call($method, array $args)
    {
        return $this->extensions->call($method, $args);
    }

    function route($block = null)
    {
        return $this->getRouter()->route($block);
    }
    
    function match($route, $callback)
    {
        return $this->getRouter()->match($route, $callback);
    }
    
    function get($route, $callback)
    {
        return $this->getRouter()->get($route, $callback);
    }

    function post($route, $callback)
    {
        return $this->getRouter()->post($route, $callback);
    }
    
    function put($route, $callback)
    {
        return $this->getRouter()->put($route, $callback);
    }
    
    function delete($route, $callback)
    {
        return $this->getRouter()->delete($route, $callback);
    }
    
    /**
     * Registers an extension for the DSL
     *
     * @see ExtensionManager
     * @param object $extension
     */
    protected function register($extension)
    {
        $this->extensions->register($extension);
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
    
    /**
     * Returns a Router instance
     *
     * @return Router
     */
	protected function getRouter()
	{
	    if (null === $this->router) {
	        $this->router = new Router;
	    }
	    return $this->router;
	}
    
    /**
     * @return Response
     */
    protected function getResponse()
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
