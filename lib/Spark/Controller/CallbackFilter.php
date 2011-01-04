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
 * @copyright  Copyright (c) 2011 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Controller;

use Spark\HttpRequest;

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
    
    function __invoke(HttpRequest $request)
    {
        $resolver = $this->getResolver();
        $callback = $request->getCallback();
        
        if (!is_array($callback)) {
            return false;
        }
        
        $controller = array_delete_key("controller", $callback) 
            ?: $request->getMetadata("controller");
        
        $module = array_delete_key("module", $callback)
            ?: $request->getMetadata("module");
        
        $callback = $resolver->getControllerByName($controller, $module);
        
        if (false === $callback) {
            return false;
        }
        $request->setCallback($callback);
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
