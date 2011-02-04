<?php

require_once "SparkCore/HttpRequest.php";
require_once "SparkCore/HttpResponse.php";
require_once "SparkCore/HttpFilterChain.php";

use SparkCore\HttpRequest,
	SparkCore\HttpResponse,
	SparkCore\HttpFilterChain;

class SparkCore
{
	protected $middleware;
	protected $request;
	protected $response;
	protected $onError;
	
	function __construct()
	{
		$this->middleware = new HttpFilterChain;
		$this->onError    = new HttpFilterChain;
	}
	
	function __invoke()
	{
		$request  = $this->getRequest();
		$response = $this->getResponse();
		
		try {
			$this->middleware->filter($request, $response);
		} catch (\Exception $e) {
			$response->setException($e);
			$this->onError->filter($request, $response);
		}
	}
	
	function append($middleware)
	{
		$this->middleware->append($middleware);
		return $this;
	}
	
	function prepend($middleware)
	{
		$this->middleware->prepend($middleware);
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
		return $this;
	}
	
	function setResponse(HttpResponse $response)
	{
		$this->response = $response;
		return $this;
	}
	
	function getMiddleware()
	{
		return $this->middleware;
	}
	
	function setMiddleware(HttpFilterChain $filterChain)
	{
		$this->middleware = $filterChain;
		return $this;
	}
}
