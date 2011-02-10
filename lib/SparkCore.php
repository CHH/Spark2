<?php

require_once "Spark/Util.php";
require_once "SparkCore/Util.php";

autoload('SparkCore\Exception',         __DIR__ . "/SparkCore/Exception.php");
autoload('SparkCore\NotFoundException', __DIR__ . "/SparkCore/NotFoundException.php");

autoload('SparkCore\Error', __DIR__ . "/SparkCore/Error.php");

require_once "SparkCore/Http.php";
require_once "SparkCore/FilterChain.php";

use SparkCore\Http\Request,
    SparkCore\Http\RequestInterface,
	SparkCore\Http\Response,
	SparkCore\Http\ResponseInterface,
	SparkCore\FilterChain,
	SparkCore\Error,
	SparkCore\NotFoundException;

function SparkCore()
{
    static $instance;

    if (null === $instance) {
        $instance = new SparkCore;
    }
    return $instance;
}

class SparkCore
{
    /** @var FilterChain */
	protected $stack;

	/** @var SplStack */
	protected $errorHandlers;

	/** @var Request */
	protected $request;

	function __construct()
	{
		$this->stack = new FilterChain;
		$this->errorHandlers = new SplStack;
	}

    /**
     * Runs the Middleware
     *
     * @param  mixed $app Class name or instance of an app to run
     * @return ReturnValues
     */
    function run($app = null)
    {
        $request = $this->getRequest();

        if (null !== $app) {
            // Try to instantiate if a classname is given
            if (is_string($app) and class_exists($app)) {
                $app = new $app;
            }
            $this->add($app);
        }
        
        if (0 === count($this->stack)) {
            trigger_error("Stack is empty, no handlers set", E_USER_NOTICE);
        }

		ob_start();
	    $response = new Response;
		
		try {
			$returnValues = $this->stack->filterUntil(
			    $request, array($request, "isDispatched")
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
			$error = new Error("An Exception occured: {$e->getMessage()}", $request, $e);
			
			foreach ($this->errorHandlers as $handler) {
                call_user_func($handler, $error);
            }
		}
	    
	    $response->appendContent(ob_get_clean());
	    $response->send();
	    return $this;
    }
	
	/**
	 * Add middleware to the loop
	 *
	 * @param  callback $middleware
	 * @return SparkCore
	 */
	function add($middleware)
	{  
	    if (func_num_args() > 1) {
            foreach (func_get_args() as $middleware) {
                $this->stack->append($middleware);
            }
            return $this;
	    }
		$this->stack->append($middleware);
		return $this;
	}
	
	/**
	 * Registers an error handler
	 *
	 * Error handlers receive as their first and only argument an Error Object which
	 * holds a message, the request object and the exception if one was thrown.
	 *
	 * @param  callback $handler
	 * @return SparkCore
	 */
	function error($handler)
	{
	    if (!is_callable($handler)) {
	        throw new InvalidArgumentException("You must supply a callback as error handler");
	    }
		$this->errorHandlers->push($handler);
		return $this;
	}
    
    /**
     * Add multiple error handlers
     *
     * @param  array $handlers List of callbacks
     * @return SparkCore
     */
    function addErrorHandlers(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->error($handler);
        }
        return $this;
    }
    
    /**
     * Returns the request instance
     *
     * @return Request
     */
    function getRequest()
    {
        if (null === $this->request) {
            $this->request = new Request;
        }
        return $this->request;
    }
    
    /**
     * Inject a custom Request instance
     *
     * @param  Request $request
     * @return SparkCore
     */
    function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }
}
