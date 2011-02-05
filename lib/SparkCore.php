<?php

require_once "SparkCore/Util.php";
require_once "SparkCore/Request.php";
require_once "SparkCore/Response.php";
require_once "SparkCore/FilterChain.php";

use SparkCore\Request,
	SparkCore\Response,
	SparkCore\FilterChain;

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
    
    function run()
    {
        $request  = $this->getRequest();
		$response = $this->getResponse();

        if (0 === count($this->stack)) {
            trigger_error("Stack is empty, no handlers set", E_USER_NOTICE);
        }
		
		try {
			$returnValues = $this->stack->filterUntil(
			    $request, $response, array($request, "isDispatched")
			);
		} catch (\Exception $e) {
			$response->setException($e);
			$this->errorHandlers->filter($request, $response);
		}
		return $returnValues;
    }
	
	function __invoke()
	{
		return $this->run();
	}

    function prepend($middleware)
	{
		$this->stack->prepend($middleware);
		return $this;
	}
	
	function append($middleware)
	{
		$this->stack->append($middleware);
		return $this;
	}
	
	function error($middleware)
	{
		$this->onError->append($middleware);
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
