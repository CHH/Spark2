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

/** @namespace */
namespace Spark
{
    require_once "Symfony/Component/ClassLoader/UniversalClassLoader.php";
    require_once "Underscore.php/underscore.php";

    use Symfony\Component\ClassLoader\UniversalClassLoader;

    /*
     * Register the Autoloader
     */
    $classLoader = new UniversalClassLoader;
    $classLoader->registerNamespaceFallback(__DIR__);
    $classLoader->register();

    function Application()
    {
        static $instance;

        if (null === $instance) {
            $instance = new \Spark\App;
        }
        return $instance;
    }

    /**
     * Delegates the given functions to the App instance
     *
     * @param string|array $method
     */
    function delegate($method)
    {
        if (!is_array($method)) {
            $method = array($method);
        }

        $template = <<<'PHP'
            namespace Spark {
                function %1$s() {
                    return call_user_func_array(array(Application(), '%1$s'), func_get_args());
                }
            }
PHP;

        foreach ($method as $m) {
            eval(sprintf($template, $m));
        }
    }

    // Delegate core methods
    delegate(array(
        "get", "post", "put", "delete", "head", "options", "before", "after",
        "error", "notFound", "run", "set", "settings", "extensions", "halt", "pass",
        "provides", "userAgent", "helpers"
    ));

    function register($extension)
    {
        if (is_string($extension) and class_exists($extension)) {
            $extension = new $extension;
        }
        Application()->register($extension);

        delegate($extension->exports());
    }
}

