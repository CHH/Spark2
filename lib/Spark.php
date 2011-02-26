<?php
/**
 * Spark Framework
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Core
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */
require_once __DIR__ . "/Spark/Util.php";
require_once "Symfony/Component/ClassLoader/UniversalClassLoader.php";

/*
 * Simple fallback autoloader
 */

use Spark\App,
    Spark\Http\Request,
    Symfony\Component\ClassLoader\UniversalClassLoader;

$classLoader = new UniversalClassLoader;

$classLoader->registerNamespaceFallback(__DIR__);

$classLoader->register();
