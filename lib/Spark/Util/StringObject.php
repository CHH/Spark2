<?php
/**
 * Utility functions for string handling
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Util
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */
namespace Spark\Util;

class StringObject implements Countable
{
    protected $value = "";
    
    function __construct($input)
    {
        if (!is_string($input)) {
            throw new \InvalidArgumentException("No String given as input for StringObject");
        }
        $this->value = $input;
    }
    
    function count()
    {
        return $this->getLength();
    }
    
    function getLength()
    {
        return strlen($this->value);
    }
    
    function toLower()
    {
        $this->value = strtolower($this->value);
    }
    
    function toUpper()
    {
        $this->value = strtoupper($this->value);
        return $this;
    }
    
    function camelize($string)
    {
        $this->value = str_camelize($string);
        return $this;
    }
    
    function exchangeString($otherString)
    {
        if (!is_string($otherString)) {
            throw new \InvalidArgumentException("No String given as input for StringObject");
        }
        $this->value = $otherString;
        return $this;
    }
    
    function getStringCopy()
    {
        return $this->value;
    }
    
    function __toString()
    {
        return $this->value;
    }
}
