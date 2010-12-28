<?php

require_once('Util\ClassLoader.php');
require_once('Util\Functions.php');

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
