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

    function __invoke($event, Array $memos = array())
    {
        if ($event !== $this->event) return true;

        $callback = $this->getCallback();
        return $callback($memos);
    }

    protected function getCallback()
    {
        $callback = $this->callback;
        if (!is_callable($callback)) {
            throw new \UnexpectedValueException("No valid Callback");
        }
        return function(Array $args = array()) use ($callback) {
            return call_user_func_array($callback, $args);
        };
    }
}
