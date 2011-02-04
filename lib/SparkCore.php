<?php

require_once "SparkCore/HttpRequest.php";
require_once "SparkCore/HttpResponse.php";
require_once "SparkCore/HttpFilterChain.php";

use SparkCore\HttpRequest,
	SparkCore\HttpResponse,
	SparkCore\HttpFilterChain;

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
    /** @var HttpFilterChain */
	protected $stack;

	/** @var HttpFilterChain */
	protected $errorHandlers;

	/** @var HttpRequest */
	protected $request;

	/** @var HttpResponse */
	protected $response;
	
	function __construct()
	{
		$this->stack = new HttpFilterChain;
		$this->errorHandlers = new HttpFilterChain;

		$this->stack->until(function(HttpRequest $request, HttpResponse $response) {
		    return $request->isDispatched();
		});
	}
    
    function run()
    {
        $request  = $this->getRequest();
		$response = $this->getResponse();

        if (0 === count($this->stack)) {
            trigger_error("Stack is empty, no handlers set", E_USER_NOTICE);
        }
		
		try {
			$returnValues = $this->stack->filter($request, $response);
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
			$this->request = new HttpRequest;
		}
		return $this->request;
	}
	
	function setRequest(HttpRequest $request)
	{
		$this->request = $request;
		return $this;
	}
	
	function getResponse()
	{
		if (null === $this->response) {
			$this->response = new HttpResponse;
		}
		return $this->response;
	}
	
	function setResponse(HttpResponse $response)
	{
		$this->response = $response;
		return $this;
	}
}
