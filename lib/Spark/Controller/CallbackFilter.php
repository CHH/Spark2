<?php
/**
 * Filter which maps module/controller callbacks to Instances of the controllers
 * via the Resolver
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Controller
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Controller;

use Spark\Http\Request,
    Spark\Util;

/**
 * Filter for use with Spark\App, which takes an callback of the form 
 * array("controller" => "foo", "module" => "bar") and looks up controllers 
 * through the attached Resolver
 *
 * @category Spark
 * @package  Spark_Controller
 */
class CallbackFilter
{
    /** @var Resolver */
    protected $resolver;
    
    function __invoke(Request $request)
    {
        $resolver = $this->getResolver();
        $callback = $request->attributes->get("_callback");

        if (is_string($callback) and false !== strpos($callback, "#")) {
            list($controller, $action) = explode("#", $callback);
            
        } else if (is_array($callback)) {
            $controller = Util\array_delete_key("controller", $callback);
            $action     = Util\array_delete_key("action", $callback);
        } else {
            return false;
        }
        
        $controller = $request->attributes->get("controller") ?: $controller;
        $action     = $request->attributes->get("action")     ?: $action;
        $module     = $request->attributes->get("module")     ?: $request->attributes->get("scope");
        
        $callback = $resolver->getControllerByName($controller, $module);
        
        if (false === $callback) {
            return false;
        }
        $request->attributes->set("action",     $action);
        $request->attributes->set("controller", $controller);
        $request->attributes->set("_callback", $callback);
    }

    function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

    function getResolver()
    {
        if (null === $this->resolver) {
            $this->resolver = new StandardResolver;
        }
        return $this->resolver;
    }
}
