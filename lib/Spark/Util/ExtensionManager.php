<?php
/**
 * Manager for extension methods
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Util
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Util;

use Spark\Extension\Base;

class ExtensionManager extends ArrayObject
{
    /**
     * @var \Spark\App
     */
    protected $app;    
    
    function __construct(\Spark\App $app)
    {
        $this->app = $app;
    }    
    
    /**
     * Exports the public methods of class as extension methods
     *
     * @param string|object $extension
     * @param array $export Optional, define explicitly which methods should be exported
     * @return ExtensionManager
     */
    function register($extension)
    {
        if (is_string($extension) and class_exists($extension)) {
            $extension = new $extension;
        }
        if (!$extension instanceof Base) {
            throw new \InvalidArgumentException("An Extension must be an instance of Extension\Base");
        }

        $extension->application($this->app);
        $methods = $extension->exports();
        
        foreach ($methods as $method) {
            $this[$method] = array($extension, $method);
        }
        return $this;
    }
    
    function has($method)
    {
        return isset($this[$method]);
    }
    
    function call($method, array $args = array())
    {
        if (!$this->has($method)) {
            throw new \BadMethodCallException("No extension provides $method");
        }
        return call_user_func_array($this[$method], $args);
    }

    function __call($method, array $args)
    {
        return $this->call($method, $args);
    }
}
