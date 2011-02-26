<?php
/**
 * The Kernel
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_View
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

use Spark\App,
    Spark\Http\Request;

class Kernel
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
            static::$request = Request::createFromGlobals();
        }
        return static::$request;
    }
}
