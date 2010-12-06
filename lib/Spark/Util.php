<?php
/**
 * Splits the string on spaces and returns the array
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
 * Wrap a function in another function
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
 * @return string
 */
function str_camelize($string)
{
    $string = str_replace(array("-", "_"), " ", strtolower($string));
    $string = ucwords($string);
    $string = str_replace(" ", null, $string);
    return $string;
}

autoload('Spark\Util\ArrayObject',  __DIR__ . '/Util/ArrayObject.php');
autoload('Spark\Util\StringObject', __DIR__ . '/Util/StringObject.php');
