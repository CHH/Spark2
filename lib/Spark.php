<?php

/*
 * Implementation of explicit Autoloading pattern (similar to Ruby's autoload())
 *
 * Every class which should be autoloaded must be registered by a call to
 * autoload(). This satisfies the needs for easy understandable class dependencies
 * and Lazy loading.
 */
$_autoload_map = array();

/**
 * Function which loads the classes which were declared as autoloadable
 *
 * @param  string $symbol The name of the class or interface which was accessed
 * @return bool
 */
function _autoload($symbol)
{
    global $_autoload_map;
    
    if (!isset($_autoload_map[$symbol])) return false;
    
    require($_autoload_map[$symbol]);
}

spl_autoload_register("_autoload");

/**
 * Declares a class or interface as autoloadable and registers it in the 
 * symbol map
 *
 * @param  string $symbol  Name of the class or interface
 * @param  string $require File which should get required if the Symbol is used
 * @return void
 */
function autoload($symbol, $require)
{
    global $_autoload_map;
    $_autoload_map[$symbol] = $require;
}

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

/*
 * Default includes
 */
autoload('Spark\Exception',          'Spark/Exception.php');
autoload('Spark\Util\ArrayObject',  'Spark/Util/ArrayObject.php');
autoload('Spark\Util\StringObject', 'Spark/Util/StringObject.php');
autoload('Spark\Options',             'Spark/Options.php');

require_once('Spark/App.php');
