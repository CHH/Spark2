<?php
/**
 * Callback Dispatcher
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_App
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

/** @namespace */
namespace Spark;

use Spark\Http\Request,
    Spark\Http\Response,
    Spark\Util\FilterChain;

class Dispatcher
{
    protected $before;
    protected $after;
    
    function __construct()
    {
        $this->before = new FilterChain;
        $this->after  = new FilterChain;
    }

    /**
     * Dispatches the request to the request's callback
     *
     * @param  Request $request
     * @return Response|void Returns the response from the callback, if any
     */
    function __invoke(Request $request)
    {
        $callback = $this->validateCallback($request->attributes->get("_callback"));

        $this->before->filter(array($request));

        ob_start();
        $response = $callback($request);
        
        if (!$response instanceof Response) {
            $response = new Response;
        }
    
        $response->write(ob_get_clean());
        $this->after->filter(array($request, $response));

        return $response;
    }

    function before($filter)
    {
        $this->before->add($filter);
        return $this;
    }

    function after($filter)
    {
        $this->after($filter);
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
            $callback = function($request) use ($callback) {
                return call_user_func($callback, $request);
            };
        }
        return $callback;
	}
}
