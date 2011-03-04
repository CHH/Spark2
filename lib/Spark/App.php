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
    Symfony\Component\HttpFoundation\RequestMatcher,
    Spark\Dispatcher,
    Spark\Util\FilterChain,
    Spark\Util;

class App
{
    /** @var \Spark\Util\ExtensionManager */
    protected $extensions; 

    protected $routes = array();
    
    /** @var array */
    protected $filters = array();
    
    protected $request;
    
    /** @var Response */
    protected $response;
    
    /** @var Spark\Settings */
    protected $settings;
    
    protected $error = array();
    
	final function __construct()
	{
        $this->extensions = new \Spark\Util\ExtensionManager($this);
        $this->settings = new \Spark\Settings;
        
        $this->request = Request::createFromGlobals();
        
        $this->register("\Spark\Extension\Templates");
        $this->register("\Spark\Extension\Redirecting");
        
        foreach (Util\words("before after shutdown") as $w) {
            $this->filters[$w] = new FilterChain;
        }
        
        $this->init();
    }
    
    function addFilter($event, $handler)
    {
        $this->filters[$event]->add($handler);
    }
    
    /**
     * Template Method which can be used to initialize Subclasses
     */
    function init()
    {}
    
    /**
     * Invokes the given callback and captures the response
     *
     * @param callback $callback
     * @param array $args
     */
    protected function getResponseCapturer($callback)
    {
        $response = $this->response();
        $self = $this;
        
        return function() use ($callback, $response, $self) {
            $args = func_get_args();
        
            ob_start();
            
            try {
                $return = call_user_func_array($callback, $args);
                $self->halt($return);
                
            } catch (\Spark\HaltException $e) {
                $return = $e->getResponse();
                $response->write(ob_get_clean());
                
                $response->write($return->getContent());
                $response->setStatusCode($return->getStatusCode());
                $response->headers->add($return->headers->all());
            }
        };
    }
    
    protected function evalConditions(array $conditions, Request $request)
    {
        $result = false;
        
        foreach ($conditions as $condition) {
            $result = $condition($request);
        }
        
        if (!$result) {
            $this->pass();
        }
        return true;
    }
    
    protected function dispatch(Request $request)
    {
        $method = $request->getMethod();
		
	    if (empty($this->routes[$method])) {
	        $this->response()->setStatusCode(404);
	    }
	    
	    $match = false;
	    
	    foreach ($this->routes[$method] as $route) {
            try {
	            list($pattern, $callback, $conditions) = $route;
	            
	            if (!preg_match($pattern, $request->getRequestUri(), $matches)) {
	                continue;
	            }
	            
                if (!empty($conditions)) {
                    $this->evalConditions($conditions, $request);
                }
                
                $callback = $this->getResponseCapturer($callback);
                $callback($request);
                
                unset($matches[0]);
                $match = true;
                break;
            } catch (\Spark\PassException $e) {
                continue;
            }
	    }
	    
	    if (!$match) {
	        $this->response()->setStatusCode(404);
	    }
	    
        $request->attributes->add($matches);
        return $callback;
    }
    
    /**
     * Dispatches the request and sends the Response
     *
     * @param  Request $request 
     * @return App|Response Returns the response if "return_response" is TRUE
     */
    function run(Request $request = null)
    {
        $request  = $request ?: $this->request;
		$response = $this->response();
		
		dispatch:
	        try {
	            $this->filters["before"]->filter(array($request, $response));  
                $this->dispatch($request);
                
                if (!$response->isSuccessful()) {
                    throw new \Exception("Request not successful.", $response->getStatusCode());
                }
            } catch (\Exception $e) {
                $code = $e->getCode() ?: get_class($e);
                
                if (!$this->handleError($code, $request, $response, $e)) {
                    goto finish;
                }
            }
		
		after:
	        $this->filters["after"]->filter(array($request, $response));
        
        finish:
	        $response->send();
    }
    
    /**
     * Call extensions
     */
    function __call($method, array $args)
    {
        if (in_array($method, array("get", "post", "put", "delete", "head", "options"))) {
            $route = array_shift($args);
            $callback = array_pop($args);
            return $this->route(strtoupper($method), $route, $args, $callback);
        }
        return $this->extensions->call($method, $args);
    }
    
    function halt($response)
    {   
        if (is_int($response)) {
            $response = new Response('', $response);
            
        } else if (is_string($response) and !empty($response)) {
            $response = new Response($response);
            
        } else if (empty($response)) {
            $response = new Response;
        }
    
        if (!$response instanceof Response) {
            throw new \InvalidArgumentException("You must either supply a code, " 
                . "content or a Response object");
        }
        
        throw new HaltException($response);
    }
    
    function pass()
    {
        throw new \Spark\PassException;
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
        $this->settings->set($spec, $value);
        return $this;
    }
    
    function settings()
    {
        return $this->settings;
    }
    
    protected function route($verb, $path, array $conditions = array(),  $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Callback is not valid");
        }
        if (empty($this->routes[$verb])) {
            $this->routes[$verb] = new \SplStack;
        }
        
        $exp = new \Spark\Util\StringExpression($path);
        $pattern = $exp->toRegExp();
        
        $this->routes[$verb]->push(array($pattern, $callback, $conditions));
    }
    
    protected function parseConditions(array $conditions)
    {
        $compiled = array();
        
        foreach ($conditions as $condition => $args) {
            if (is_callable(array($this, $condition))) {
                $compiled[] = $this->{$condition}($args);
            } else if ($this->settings->get($condition)) {
                // Handle user defined conditions
            }
        }
        
        return $compiled;
    }
    
    function extensions()
    {
        return $this->extensions;
    }
    
    /**
     * Registers an extension for the DSL
     *
     * @see ExtensionManager
     * @param object $extension
     */
    function register($extension)
    {
        $this->extensions()->register($extension);
    }
    
    /**
     * Attaches a filter to the filters run before dispatching
     *
     * @param  object $filter Callable object (Closure or Object implementing __invoke)
     * @return App
     */
    function before($handler)
    {
        $this->filters["before"]->add($handler);
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
	    $this->filters["after"]->add($handler);
	    return $this;
	}
    
    protected function handleError($code = "\Exception", $request, $response, $exception = null)
    {   
        $error = new \StdClass;
        $error->request = $request;
        $error->response = $response;
        $error->exception = $exception;
        
        $handler = empty($this->errors[$code]) ? null : $this->errors[$code];
        
        if (!$handler) {
            return false;
        }
        
        $handler = $this->getResponseCapturer($handler);
        $handler($error);
    }
    
    /**
     * Registers an error handler
     */
    function error($code = "\Exception", $handler = null) 
    {
        if (is_callable($code)) {
            $handler = $code;
            $code = "\Exception";
        }
		if (!is_array($code)) {
		    $code = array($code);
		}
	    foreach ($code as $c) {
	        $this->errors[$c] = $handler;
	    }
		return $this;
    }
    
    /**
     * Registers an handler on the error code 404
     *
     * @param  callback $callback
     * @return App
     */
    function notFound($callback) {
        return $this->error(404, $callback);
    }
    
    /*
     * Bundled Route Conditions
     */
    
    function hostName($pattern)
    {
        return function(Request $request) use ($pattern) {
            $hostname = $request->getHost();
            
            return preg_match($pattern, $hostname, $matches) > 0;
        };
    }
    
    function userAgent($pattern)
    {
        return function(Request $request) use ($pattern) {
            $userAgent = $request->headers->get("user-agent");
            
            return preg_match($pattern, $userAgent, $matches) > 0;
        };
    }
    
    function provides($mimetypes)
    {
        $mimetypes = func_get_args();
        
        return function(Request $request) use ($mimetypes) {
            $accepts = $request->getAcceptableContentTypes();
            
            // Check if the given mimetypes match the request header's
        };
    }
    
    /**
     * @return Response
     */
    protected function response()
    {
        if (null === $this->response) {
            $this->response = new Response;
        }
        return $this->response;
    }
}
