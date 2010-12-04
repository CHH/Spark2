<?php

namespace Spark\Event;

class Handler
{
    protected $event;
    protected $subject;
    protected $callback;    
    
	public function __construct($event, $subject, $callback)
	{
	    $this->event    = $event;
	    $this->subject  = $subject;
	    $this->callback = $callback;
	}
	
	public function __invoke($event, $memo = null)
	{
	    if ($event !== $this->event) return true;
	    
	    $callback = $this->getCallback();
	    return $callback($this->subject, $memo);
	}
	
	public function getCallback()
	{
	    $callback = $this->callback;
	    if (!is_callable($callback)) {
	        throw new \UnexpectedValueException("No valid Callback");
	    }
	    if ($callback instanceof Closure) {
	        return $callback;
	    }
	    return function() use ($callback) {
	        return call_user_func_array($callback, func_get_args());
	    };
	}
}
