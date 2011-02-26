<?php
/**
 * The static View Renderer
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

use Spark\View\Engine,
    Spark\View\PhpEngine;

class View
{
    /** @var Engine */
    static $instance;    
    
    /**
     * Renders the given template
     */
    static function render($template, $view = null)
    {
        return static::getEngine()->render($template, $view);
    }
    
    /**
     * Adds a template path
     */
    static function setTemplatePath($path)
    {
        static::getEngine()->setTemplatePath($path);
    }
    
    /**
     * Replaces the engine
     */
    static function setEngine(Engine $engine)
    {
        static::$instance = $engine; 
    }
    
    /**
     * Returns the current engine
     */
    static function getEngine()
    {
        if (null === static::$instance) {
            static::$instance = new PhpEngine;
        }
        return static::$instance;
    }
}
