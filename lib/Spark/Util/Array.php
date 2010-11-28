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
namespace Spark\Util\Array;

function delete_key($key, &$array)
{
	if (!isset($array[$key])) {
		return null;
	}
	$value = $array[$key];
	unset($array[$key]);
	return $value;
}

function delete($value, &$array)
{
	$offset = array_search($value, $array);
	if (false === $offset) {
		return null;
	}
	unset($array[$offset]);
	return $value;
}
