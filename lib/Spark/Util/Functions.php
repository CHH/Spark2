<?php
/**
 * Utility Functions
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Util
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2011 Christoph Hochstrasser
 * @license    MIT License
 */

/**
 * Splits the string on spaces and returns the parts
 * 
 * @param  string $string
 * @return array
 */
function words($string)
{
    return explode(" ", $string);
}

/**
 * Deletes the given key from the array and returns his value
 *
 * @param  mixed $key   Key to search for
 * @param  array $array
 * @return mixed Value of the given key, NULL if key was not found in array
 */
function array_delete_key($key, &$array)
{
    if (!isset($array[$key])) {
        return null;
    }
    $value = $array[$key];
    unset($array[$key]);
    return $value;
}

/**
 * Searches the given value in the array, unsets the found offset
 * and returns the value
 *
 * @param  mixed $value Value to search for
 * @param  array $array
 * @return mixed The value or NULL if the value was not found
 */
function array_delete($value, &$array)
{
    $offset = array_search($value, (array) $array);
    if (false === $offset) {
        return null;
    }
    unset($array[$offset]);
    return $value;
}

/**
 * Wrap a function in another function and avoid a recursion by passing 
 * the wrapped function as argument to the wrapper
 *
 * @param  callback $fn      The function to wrap
 * @param  callback $wrapper A wrapper function, receives the wrapped function as
 *                           first argument and the arguments passed to the wrapped 
 *                           function as subsequent arguments
 * @return Closure
 */
function func_wrap($fn, $wrapper)
{
    // Unify calling of the wrapped function
    if(is_array($fn) or is_string($fn)) {
        $original = function() use ($fn) {
            return call_user_func_array($fn, func_get_args());
        };
    } else {
        $original = $fn;
    }
    
    $wrapped = function() use ($original, $wrapper) {
        $args = func_get_args();
        array_unshift($args, $original);
        return call_user_func_array($wrapper, $args);
    };
    
    return $wrapped;
}

/**
 * Prefills the arguments of a given function
 *
 * @param  callback $fn        Function to curry
 * @param  mixed    $value,... Arguments for currying the function
 * @return Closure
 */
function func_curry($fn)
{
    $curry = array_slice(func_get_args(), 1);
    
    return function() use ($fn, $curry) {
        $args = array_merge($curry, func_get_args());
        return call_user_func_array($fn, $args);
    };
}

/**
 * Composes multiple callback functions into one by passing each function's
 * return value as argument into the next function. The arguments passed to
 * the composed function get passed to the first (most inner) function.
 *
 * @param  callback $fn,... Functions to compose
 * @return Closure
 */
function func_compose()
{
    $fns = func_get_args();
    
    return function() use ($fns) {
        $input = func_get_args();
        foreach ($fns as $fn) {
            $returnValue = call_user_func_array($fn, $input);
            $input = array($returnValue);
        }
        return $returnValue;
    };
}

/**
 * Looks by default at the end of an argument list for a block (Closure)
 *
 * @param  Array $fnArgs Argument list
 * @param  mixed $offset Optional offset if block is not expected on the 
 *                       end of the argument list
 * @return bool
 */
function block_given(Array $fnArgs, $offset = null)
{
    if (null === $offset) {
        $block = array_pop($fnArgs);
    } else {
        $block = $fnArgs[$offset];
    }
    return $block instanceof Closure;
}

/**
 * Camelizes a dash or underscore separated string
 *
 * @param  string $string
 * @param  bool   $uppercaseFirst By default the first letter is uppercase
 * @return string
 */
function str_camelize($string, $uppercaseFirst = true)
{
    $string = str_replace(array("-", "_"), " ", $string);
    $string = ucwords($string);
    $string = str_replace(" ", null, $string);
    
    if (!$uppercaseFirst) {
        return lcfirst($string);
    }
    return $string;
}
