<?php
/**
 * Utility functions for array handling
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

class ArrayObject extends \ArrayObject
{
    function deleteKey($key)
    {
        if (!isset($this[$key])) {
            return null;
        }
        $value = $this[$key];
        unset($this[$key]);
        return $value;
    }

    function delete($value)
    {
        $offset = array_search($value, (array) $this);
        if (false === $offset) {
            return null;
        }
        unset($this[$offset]);
        return $value;
    }
    
    function slice($length, $start = 0)
    {
        if (!is_numeric($length)) {
            throw new \InvalidArgumentException("Length must be a valid number");
        }
        $array = $this->getArrayCopy();
        return new self(array_slice($array, $start, $length));
    }
}
