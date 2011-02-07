<?php

namespace Spark;

use SparkCore\Http\Request;

class Dispatcher
{
    function __invoke(Request $request)
    {
	    try {
            $callback = $this->validateCallback($request->getCallback());
	        return $callback($request);
	        
		} catch (\Exception $e) {
		    $response->setException($e);
		}
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
