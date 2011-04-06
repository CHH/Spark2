<?php

namespace Spark;

class Settings
{
    protected $options = array();
    
    function set($spec, $value = null)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $this->options[$key] = $value;
            }
            return $this;
        }
        
        if (!is_scalar($spec)) {
            throw new \InvalidArgumentException(sprintf(
                "Setting name must be a scalar value, %s given", gettype($spec)
            ));
        }
        
        $this->options[$spec] = $value;
        return $this;
    }
    
    function enable($setting)
    {
        return $this->set($setting, true);
    }
    
    function disable($setting)
    {   
        return $this->set($setting, false);
    }
    
    function get($key = null)
    {
        if (null === $key)
        {
            return $this->options;
        }
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }
    
    function __get($var)
    {
        return $this->get($var);
    }
}
