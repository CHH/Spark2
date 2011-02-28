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
    
    /** @var Response */
    protected $response;
    
    /** @var Spark\Settings */
    protected $settings;
    
	final function __construct()
	{
        $this->extensions = new \Spark\Util\ExtensionManager($this);
        $this->settings = new \Spark\Settings;
    
        $this->register("\Spark\Extension\ViewRenderer");
        $this->register("\Spark\Extension\Redirecting");
        
        foreach (Util\words("before after error notFound shutdown") as $w) {
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
		    $this->filters["before"]->filter(array($request, $response));
		    
		    $method = $request->getMethod();
		    
		    if (empty($this->routes[$method])) {
		        $response->setStatusCode(404);
		        $response->setContent('');
		        break;
		    }
		    
		    $requestMatcher = new RequestMatcher;
		    
		    foreach ($this->routes[$method] as $route) {
		        list($pattern, $callback) = $route;
		        
		        $requestMatcher->matchPath($pattern);
		        
		        if ($requestMatcher->matches($request)) {
		            break;
		        }
		    }
		    
		    $return = call_user_func($callback, $request);
		    
		    if (!$return instanceof Response) {
		        $return = new Response($return);
		    }
		    
		    $returnValues[] = $return;
		} catch (\Exception $e) {
            if ($this->filters["error"]->isEmpty()) {
                throw $e;
            }
		
			$error = new \StdClass;
			$error->exception = $e;
			$error->request = $request;
			$error->response = $response;

			ob_start();
			foreach ($this->filters["error"]->filter(array($error)) as $return) {
                $returnValues[] = $return;
			}
			
			$response->write(ob_get_clean());
		}

	    ob_start();
        $this->filters["after"]->filter(array($request, $response));
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
	    
	    if ($response->isNotFound()) {
	        $this->filter["notFound"]->filter(array($request, $response));
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
    
    protected function route($verb, $path, array $options = array(),  $callback)
    {
        if (empty($this->routes[$verb])) {
            $this->routes[$verb] = new \SplStack;
        }
        $pattern = $this->compile($path, $options);
        
        $this->routes[$verb]->push(array($path, $callback));
        return $this;
    }
    
    protected function compile($path, array $options = array())
    {
        $exp = new \Spark\Util\StringExpression($path, $options);
        return $exp->toRegExp(false);
    }
    
    function get($route, $callback)     { return $this->route("GET", $route, array(), $callback); }   
    function post($route, $callback)    { return $this->route("POST", $route, array(), $callback); }
    function put($route, $callback)     { return $this->route("PUT", $route, array(), $callback); }
    function delete($route, $callback)  { return $this->route("DELETE", $route, array(), $callback); }
    function head($route, $callback)    { return $this->route("HEAD", $route, array(), $callback); }
    function options($route, $callback) { return $this->route("OPTIONS", $route, array(), $callback); }
    
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

    /**
     * Registers an error handler
     */
    function error($handler) {
		$this->filters["error"]->add($handler);
		return $this;
    }
    
    /**
     * Registers an handler on the error code 404
     *
     * @param  callback $callback
     * @return App
     */
    function notFound($callback) {
        
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
}
