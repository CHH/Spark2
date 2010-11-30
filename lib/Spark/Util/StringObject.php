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
    
    public function __construct($input)
    {
        if (!is_string($input)) {
            throw new \InvalidArgumentException("No String given as input for StringObject");
        }
        $this->value = $input;
    }
    
    public function count()
    {
        return $this->getLength();
    }
    
    public function getLength()
    {
        return strlen($this->value);
    }
    
    public function toLower()
    {
        $this->value = strtolower($this->value);
    }
    
    public function toUpper()
    {
        $this->value = strtoupper($this->value);
        return $this;
    }
    
    public function camelize($string)
    {
	    $string = str_replace(array("-", "_"), " ", strtolower($this->value));
	    $string = ucwords($string);
	    $this->value = str_replace(" ", null, $string);
	    return $this;
    }
    
    public function exchangeString($otherString)
    {
        if (!is_string($otherString)) {
            throw new \InvalidArgumentException("No String given as input for StringObject");
        }
        $this->value = $otherString;
        return $this;
    }
    
    public function getStringCopy()
    {
        return $this->value;
    }
    
    public function __toString()
    {
        return $this->value;
    }
}
