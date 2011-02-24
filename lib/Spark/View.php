<?php
/**
 * The View Renderer Facade
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

autoload("Spark\View\Engine", __DIR__ . "/View/Engine.php");
autoload("Spark\View\PhpEngine", __DIR__ . "/View/PhpEngine.php");
autoload("Spark\View\PhlyMustacheEngine", __DIR__ . "/View/PhlyMustacheEngine.php");

use Spark\View\Engine,
    Spark\View\PhpEngine;

class View
{
    static $instance;    
    
    static function render($template, $view = null)
    {
        return static::getEngine()->render($template, $view);
    }
    
    static function setTemplatePath($path)
    {
        static::getEngine()->setTemplatePath($path);
    }
    
    static function setEngine(Engine $engine)
    {
        static::$instance = $engine; 
    }
    
    static function getEngine()
    {
        if (null === static::$instance) {
            static::$instance = new PhpEngine;
        }
        return static::$instance;
    }
}
