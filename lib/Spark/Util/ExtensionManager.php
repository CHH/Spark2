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

class ExtensionManager extends ArrayObject
{
    protected $context;    
    
    function __construct($context)
    {
        $this->context = $context;
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
        if (is_string($extension) and !empty($extension)) {
            $extension = new $extension;
        }
        if (!is_object($extension)) {
            throw new \InvalidArgumentException("An Extension must be an object or a class name");
        }

        // Look if the extension defines which methods should be exported,
        // else take all public methods
        if (!empty($extension->__export)) {
            $methods = $extension->__export;
        } else {
            $methods = get_class_methods($extension);
        }
        
        if ($extension instanceof \Spark\Extension\Base) {
            $extension->context = $this->context;
        }
        
        foreach ($methods as $method) {
            // Don't register magic methods
            if ("__" == substr($method, 0, 2)) {
                continue;
            }
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
