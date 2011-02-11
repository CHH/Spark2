<?php

namespace Spark;

use Spark\Http\Request,
    Spark\Http\FilterChain;

class Dispatcher
{
    protected $before;
    protected $after;    
    
    function __construct()
    {
        $this->before = new FilterChain;
        $this->after  = new FilterChain; 
    }
    
    function __invoke(Request $request)
    {
        $callback = $this->validateCallback($request->getCallback());
        
        $this->before->filter($request);
        $response = $callback($request);
        $this->after->filter($request);
        
        $request->setDispatched();
        return $response;
    }
    
    function before($callback = null)
    {
        if (null === $callback) {
            return $this->before;
        }
        return $this->before->append($callback);
    }
    
    function after($callback = null)
    {
        if (null === $callback) {
            return $this->after;
        }
        $this->after->append($callback);
    }
    
    /**
     * Validates if the callback is callable and wraps array style callbacks
     * in a closure to allow closure-style calling
     *
     * @param  mixed $callback
     * @return Closure
     */
	protected function validateCallback($callback)
	{
        if (!is_callable($callback)) {
            throw new \RuntimeException("The callback is not valid");
        }
        
        if (is_array($callback) or is_string($callback)) {
            $callback = function($request, $response) use ($callback) {
                return call_user_func($callback, $request, $response);
            };
        }
        return $callback;
	}
}
