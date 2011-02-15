<?php
/**
 * Simple object representing a string, inspired by ArrayObject
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Util
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */
namespace Spark\Util;

use Countable;

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
        return strlen($this->value);
    }
    
    function toLower()
    {
        $this->value = strtolower($this->value);
        return $this;
    }
    
    function toUpper()
    {
        $this->value = strtoupper($this->value);
        return $this;
    }
    
    function camelize()
    {
        $this->value = str_camelize($this->value);
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
