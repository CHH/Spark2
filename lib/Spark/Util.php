<?php
/**
 * Util Package
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
require_once('Util/ClassLoader.php');
require_once('Util/Functions.php');

use Spark\Util;

Util\ClassLoader()->register();

/**
 * Declares a class or interface as autoloadable and registers it in the 
 * autoload class map
 *
 * @param  string $symbol  Name of the class or interface
 * @param  string $require File which should get required if the Symbol is used
 * @return void
 */
function autoload($symbol, $require)
{
	Util\ClassLoader()->registerSymbol($symbol, $require);
}

autoload('Spark\Util\ArrayObject',  __DIR__ . '/Util/ArrayObject.php');
autoload('Spark\Util\StringObject', __DIR__ . '/Util/StringObject.php');
autoload('Spark\Util\Options',      __DIR__ . '/Util/Options.php');
