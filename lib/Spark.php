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
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */
require_once('SparkCore.php');
require_once('Spark/App.php');

use Spark\App;

/**
 * Implements a Singleton for Spark\App
 *
 * @param  Spark\App $app Inject your own App instance
 * @return Spark\App
 */
function Spark(App $app = null)
{
    static $instance;

    if (null === $instance) {
        if (null !== $app) {
            $instance = $app;
        } else {
            $instance = new App;
        }
    }
    return $instance;
}

/* 
 * Bootstrap the framework
 */
$core  = SparkCore();
$spark = Spark();

$router = $spark->getRouter();
$dispatcher = $spark->getDispatcher();
$preDispatch = $spark->getPreDispatch();
$postDispatch = $spark->getPostDispatch();

$core->append($preDispatch)
     ->append($router)
     ->append($dispatcher)
     ->append($postDispatch);

