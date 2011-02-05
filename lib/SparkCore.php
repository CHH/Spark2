<?php

require_once "SparkCore/Util.php";
require_once "SparkCore/Framework.php";
require_once "SparkCore/Request.php";
require_once "SparkCore/Response.php";
require_once "SparkCore/FilterChain.php";

use SparkCore\Request,
	SparkCore\Response,
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
	protected $errorHandlers;

	/** @var Request */
	protected $request;

	/** @var Response */
	protected $response;
	
	function __construct()
	{
		$this->stack = new FilterChain;
		$this->errorHandlers = new FilterChain;
	}
    
    function run($framework = null)
    {
        $request  = $this->getRequest();
		$response = $this->getResponse();

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
			$response->setException($e);
			$this->errorHandlers->filter($request, $response);
		}
	    $response->append(ob_get_clean())->send();
		return $returnValues;
    }
	
	function __invoke()
	{
		return $this->run();
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
		$this->errorHandlers->append($middleware);
		return $this;
	}

    function setErrorHandlers(FilterChain $handlers)
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
	
	function getResponse()
	{
		if (null === $this->response) {
			$this->response = new Response;
		}
		return $this->response;
	}
	
	function setResponse(Response $response)
	{
		$this->response = $response;
		return $this;
	}
}
