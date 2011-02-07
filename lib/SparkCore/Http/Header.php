<?php

namespace SparkCore\Http;

class Header
{
    protected $type;
    protected $value;
    protected $replace = false;
    
    function __construct($header, $value = null, $replace = false)
    {
        $this->type = $this->normalizeHeader($header);
        $this->value = $value;
        $this->replace = $replace;
    }
    
    function replace($flag = null)
    {
        if (null === $flag) {
            return $this->replace;
        }
        $this->replace = $flag;
        return $this;
    }
    
    function send()
    {
        header($this->type . ": " . $this->value, $this->replace());
        return $this;
    }
    
    function getType()
    {
        return $this->type;
    }
    
    function getValue()
    {
        return $this->value;
    }
    
    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeHeader($name)
    {
        $filtered = str_replace(array('-', '_'), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }
}
