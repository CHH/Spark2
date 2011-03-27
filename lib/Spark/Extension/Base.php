<?php
/**
 * Extension Base Class
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

namespace Spark\Extension;

class Base
{
    /**
     * Application this Extension runs in
     * @var \Spark\App
     */
    public $app;
    
    /**
     * @var \Spark\Util\ExtensionManager
     */
    public $manager;
    
    /**
     * Hook which gets called after the extension was registered
     */
    function registered(\Spark\App $app)
    {}
    
    function exports()
    {
        $self = __CLASS__;

        return array_filter(get_class_methods($this), function($method) use ($self) {
            return substr($method, 0, 2) != "__" and !in_array($method, get_class_methods($self));
        });
    }
    
    function __get($var)
    {
        if (isset($this->app->{$var})) {
            return $this->app->{$var};
        }
    }
    
    function __call($method, array $args = array())
    {
        $callable = array($this->app, $method);
        
        if (!is_callable($callable)) {
            $callable = array($this->manager, $method);
            if (!is_callable($callable)) {
                throw new \BadMethodCallException("Call to an undefined Method $method");
            }
        }
        return call_user_func_array($callable, $args);
    }
}

