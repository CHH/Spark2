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
require_once "Spark/Util.php";

autoload("Spark\Exception",  __DIR__ . "/Spark/Exception.php");
autoload("Spark\Error",      __DIR__ . "/Spark/Error.php");
autoload("Spark\Dispatcher", __DIR__ . "/Spark/Dispatcher.php");

require_once "Spark/Http.php";
require_once('Spark/Controller.php');
require_once('Spark/Router.php');
require_once('Spark/View.php');
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
    /** @var Request */
    static protected $request;

    /**
     * Instantiates the given app and calls it with an HTTP Request 
     *
     * @param  string|App $app Class name or App instance
     * @return App
     */
    static function run($app)
    {
        $app = static::factory($app);
        
        $request = static::getRequest();
        return $app($request);
    }
    
    /**
     * Instantiates the given App class
     *
     * @param  string $app Class name of a App subclass
     * @return App
     */
    static function factory($app)
    {
        if (is_string($app) and class_exists($app)) {
            $app = new $app;
        }
        if (!$app instanceof App) {
            throw new \InvalidArgumentException("App must be an instance of Spark\App");
        }
        return $app;       
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
