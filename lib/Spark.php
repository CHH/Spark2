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
    /** @var App */
    static protected $app;
    
    static protected $delegate = array(
        "get", "post", "put", "delete", "head", "options", "before", "after", 
        "error", "notFound", "register", "run", "set", "settings"
    );

    /**
     * Call an extension
     *
     * @param string $method
     * @param array $args
     */
    static function __callStatic($method, array $args)
    {
        if (!in_array($method, static::$delegate)) {
            $extensions = static::app()->extensions();
            
            if ($extensions->has($method)) {
                return $extensions->call($method, $args);
            }
            
            throw new \BadMethodCallException("Undefined Method $method");
        }
        return call_user_func_array(array(static::app(), $method), $args);
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
