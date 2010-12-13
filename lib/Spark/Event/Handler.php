<?php

namespace Spark\Event;

class Handler
{
    protected $event;
    protected $subject;
    protected $callback;    
    
	function __construct($event, $subject, $callback)
	{
	    $this->event    = $event;
	    $this->subject  = $subject;
	    $this->callback = $callback;
	}
	
	function __invoke($event, $memo = null)
	{
	    if ($event !== $this->event) return true;
	    
	    $callback = $this->getCallback();
	    return $callback($this->subject, $memo);
	}
	
	function getCallback()
	{
	    $callback = $this->callback;
	    if (!is_callable($callback)) {
	        throw new \UnexpectedValueException("No valid Callback");
	    }
	    if ($callback instanceof Closure) {
	        return $callback;
	    }
	    return function($subject, $memo = null) use ($callback) {
	        return call_user_func($callback, $subject, $memo);
	    };
	}
}
