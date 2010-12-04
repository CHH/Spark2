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
 * Camelizes a dash or underscore separated string
 *
 * @param  string $string
 * @return string
 */
function string_camelize($string)
{
    $string = str_replace(array("-", "_"), " ", strtolower($string));
    $string = ucwords($string);
    $string = str_replace(" ", null, $string);
    return $string;
}

autoload('Spark\Util\ArrayObject',  __DIR__ . '/Util/ArrayObject.php');
autoload('Spark\Util\StringObject', __DIR__ . '/Util/StringObject.php');
