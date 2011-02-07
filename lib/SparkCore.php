<?php

require_once "SparkCore/Util.php";
require_once "SparkCore/Framework.php";

require_once "SparkCore/Http/Header.php";
require_once "SparkCore/Http/Request.php";
require_once "SparkCore/Http/Response.php";

require_once "SparkCore/FilterChain.php";

use SparkCore\Http\Request,
	SparkCore\Http\Response,
	SparkCore\FilterChain,
	SparkCore\Framework;

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

	/** @var FilterChain */
	protected $errorHandlers = array();

	/** @var Request */
	protected $request;

	function __construct()
	{
		$this->stack = new FilterChain;
	}

    /**
     * Runs the Request Handlers
     *
     * @param string|Framework $framework Class or object which is used to bootstrap
     *                                    the Request Handler stack
     * @return ReturnValues
     */
    function run($framework = null)
    {
        $request  = $this->getRequest();

        if (null !== $framework) {
            if (is_string($framework) and !empty($framework)) {
                $framework = new $framework;
            }
            if (!$framework instanceof Framework) {
                throw new \InvalidArgumentException("Initializers must implement the"
                    . " SparkCore\Framework Interface");
            }
            $framework->setUp($this);
        }
        
        if (0 === count($this->stack)) {
            trigger_error("Stack is empty, no handlers set", E_USER_NOTICE);
        }

		ob_start();
		try {
			$returnValues = $this->stack->filterUntil(
			    $request, $response, array($request, "isDispatched")
			);
		} catch (\Exception $e) {
			foreach ($this->errorHandlers as $handler) {
			    call_user_func($handler, $e);
			}
		}
	    
	    $response = new Response;
	    $response->appendContent(ob_get_clean());
	    
	    if ($returnValues) {
	        foreach ($returnValues as $return) {
	            if (!$return instanceof Response) {
	                continue;
	            }
                $response->addHeaders($return->getHeaders());
                $response->appendContent($return->getContent());
	        }
	    }
	    
	    $response->send();
	    
		return $returnValues;
    }
	
    function prepend($middleware)
	{
	    if (func_num_args() > 1) {
            foreach (func_get_args() as $middleware) {
                $this->stack->prepend($middleware);
            }
            return $this;
	    }
		$this->stack->prepend($middleware);
		return $this;
	}
	
	function append($middleware)
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
	
	function error($middleware)
	{
		$this->errorHandlers[] = $middleware;
		return $this;
	}

    function setErrorHandlers(array $handlers)
    {
        $this->errorHandlers = $handlers;
        return $this;
    }
	
	function getRequest()
	{
		if (null === $this->request) {
			$this->request = new Request;
		}
		return $this->request;
	}
	
	function setRequest(Request $request)
	{
		$this->request = $request;
		return $this;
	}
}
