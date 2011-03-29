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
    require_once __DIR__ . "/../vendor/Underscore.php/underscore.php";
    require_once __DIR__ . "/../_autoload.php";

    function Application()
    {
        static $instance;

        if (null === $instance) {
            $instance = new \Spark\Application;
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
        "provides", "userAgent"
    ));

    function register($extension)
    {
        if (is_string($extension) and class_exists($extension)) {
            $extension = new $extension;
        }
        Application()->register($extension);

        delegate($extension->exports());
    }

    /**
     * Add or return helpers
     */
    function helpers($helper = null)
    {
        $helpers = Application()->helpers;
        if (null === $helper) {
            return $helpers;
        }
        foreach (func_get_args() as $helper) {
            $helpers->register($helper);
        }
    }
}

