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
require_once "Spark/Util.php";

autoload("Spark\NotFoundException", __DIR__ . "/Spark/NotFoundException.php");
autoload("Spark\Error", __DIR__ . "/Spark/Error.php");
autoload("Spark\FilterChain", __DIR__ . "/Spark/FilterChain.php");

require_once "Spark/Http.php";

require_once('Spark/App.php');

use Spark\App,
    Spark\Http\Request;

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

class Spark
{
    static protected $request;

    static function run($app)
    {
        if (is_string($app) and class_exists($app)) {
            $app = new App;
        }
        if (!$app instanceof App) {
            throw new \InvalidArgumentException("App must be an instance of Spark\App");
        }
        
        $request = static::getRequest();
        return $app($request);
    }
    
    static function setRequest(Request $request)
    {
        static::$request = $request;
    }
    
    static function getRequest()
    {
        if (null === static::$request) {
            static::$request = new Request;
        }
        return static::$request;
    }
}
