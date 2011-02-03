<?php

namespace Spark;

use Spark\HttpRequest,
    Spark\HttpResponse;

class Dispatcher
{
    function __invoke(HttpRequest $request, HttpResponse $response)
    {
        ob_start();
	    
	    try {
            $callback = $this->validateCallback($request->getCallback());
	        $callback($request, $response);
	        
		} catch (\Exception $e) {
		    $response->setException($e);
		}
		
		// Attach all stdout output from callbacks
		$response->append(ob_get_clean());
		
		$response->send();
		return $this;
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
