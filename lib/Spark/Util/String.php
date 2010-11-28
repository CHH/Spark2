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
namespace Spark\Util\String;

function camelize($string)
{
	$string = str_replace(array("-", "_"), " ", strtolower($string));
	$string = ucwords($string);
	$string = str_replace(" ", null, $string);
	return $string;
}
