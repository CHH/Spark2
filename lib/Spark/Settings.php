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
        $this->options[$spec] = $value;
        return $this;
    }
    
    function get($key = null)
    {
        if (null === $key)
        {
            return $this->options;
        }
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }
}
