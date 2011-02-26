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


use Spark\App,
    Spark\Http\Request,
    Spark\Util\ExtensionManager,
    Symfony\Component\ClassLoader\UniversalClassLoader;

/*
 * Register the Autoloader
 */
$classLoader = new UniversalClassLoader;
$classLoader->registerNamespaceFallback(__DIR__);
$classLoader->register();

/**
 * Provides the static DSL
 */
class Spark
{
    /** @var ExtensionManager */
    static protected $extensions;

    /** @var App */
    static protected $app;

    /**
     * Registers an extension for the DSL
     *
     * @see ExtensionManager
     * @param object $extension
     */
    static function register($extension)
    {
        static::extensionManager()->register($extension);
    }

    /**
     * Call an extension
     *
     * @param string $method
     * @param array $args
     */
    static function __callStatic($method, array $args)
    {
        if (is_callable(array(static::app(), $method))) {
            return call_user_func_array(array(static::app(), $method), $args);
        }
        return static::extensionManager()->call($method, $args);
    }
    
    static function run()
    {   
        $routes = static::route();
        
        static::app()->run();
    }
    
    /**
     * Returns an instance of the Extension Manager
     *
     * @return ExtensionManager
     */
    static protected function extensionManager()
    {   
        if (null === static::$extensions) {
            static::$extensions = new \Spark\Util\ExtensionManager;
        }
        return static::$extensions;
    }

    /**
     * Returns an app instance
     *
     * @return App
     */
    static protected function app()
    {
        if (null === static::$app) {
            static::$app = new App;
        }
        return static::$app;
    }
}
