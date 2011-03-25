<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;

define("LIB_PATH", __DIR__);
define("VENDOR_PATH", realpath(__DIR__ . "/../vendor"));

require_once VENDOR_PATH . "/Symfony/Component/ClassLoader/UniversalClassLoader.php";
require_once VENDOR_PATH . "/Underscore.php/underscore.php";

/*
 * Register the Autoloader
 */
$classLoader = new UniversalClassLoader;
$classLoader->registerNamespaces(array(
    "Spark"   => LIB_PATH,
    "Symfony" => VENDOR_PATH
));
$classLoader->register();

