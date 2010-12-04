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
