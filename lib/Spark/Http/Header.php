<?php
/**
 * A class which represents a single HTTP header
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

namespace Spark\Http;

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
