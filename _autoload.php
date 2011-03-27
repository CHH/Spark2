<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;

define("LIB_PATH", __DIR__ . "/lib");
define("VENDOR_PATH", __DIR__ . "/vendor");

require_once VENDOR_PATH . "/Symfony/Component/ClassLoader/UniversalClassLoader.php";

/*
 * Register the Autoloader
 */
$classLoader = new UniversalClassLoader;
$classLoader->registerNamespaces(array(
    "Spark"   => LIB_PATH,
    "Symfony" => VENDOR_PATH
));
$classLoader->register();

