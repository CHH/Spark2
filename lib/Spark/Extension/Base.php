<?php

namespace Spark\Extension;

class Base
{
    protected $context;
    
    function exports()
    {
        return array_filter(get_class_methods($this), function($method) {
            return substr($method, 0, 2) != "__" and !in_array($method, array("exports", "context"));
        });
    }
    
    function context($context = null)
    {
        if (null === $context) {
            return $this->context;
        }
        $this->context = $context;
    }
}
