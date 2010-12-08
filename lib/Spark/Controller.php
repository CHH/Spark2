<?php
/**
 * Front Controller
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Controller
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

autoload('Spark\Controller\Exception', __DIR__ . '/Controller/Exception.php');

require_once('Controller/HttpRequest.php');
require_once('Controller/HttpResponse.php');
require_once('Controller/Controller.php');
require_once('Controller/ActionController.php');
require_once('Controller/Resolver.php');
require_once('Controller/StandardResolver.php');

class Controller
{
    protected $resolver;
    protected $errorController = array('controller' => 'error', 'action' => 'error');
    
    function __construct(Array $options = array())
    {
        if ($options) {
            $this->setOptions($options);
        }
    }
    
    function __invoke(
        Controller\HttpRequest  $request, 
        Controller\HttpResponse $response
    )
    {
        try {
            $controller = $this->getResolver()->getInstance($request);
            
            if (false === $controller) {
                throw new Controller\Exception(sprintf(
                    "Controller %s not found in module %s",
                    $request->getParam("controller", "Index"),
                    $request->getParam("module", "default")
                ), 404);
            }
            
            ob_start();
            
            do {
                $request->setDispatched(true);
                $controller($request, $response);
            } while (!$request->isDispatched());
            
            $response->appendBody(ob_get_clean());
            
        } catch (Exception $e) {
            $request->setParam("exception", $e);
            $this->handleError($request, $response);
        }
        return $this;
    }
    
    function handleError($request, $response)
    {
        foreach ($this->errorController as $param => $value) {
            $request->setParam($param, $value);
        }
        $this->__invoke($request, $response);
        return $this;
    }
    
    function setOptions(Array $options)
    {
        Options::setOptions($this, $options);
        return $this;
    }
    
    function setErrorController($controller) 
    {
        $this->errorController = $controller;
        return $this;
    }
    
    function getResolver()
    {
        if (null === $this->resolver) {
            $this->resolver = new Controller\StandardResolver;
        }
        return $this;
    }
    
    function setResolver(Controller\Resolver $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }
}

function Controller(Array $options = array())
{
    static $instance;
    if (null === $instance) {
        $instance = new Controller;
    }
    if ($options) {
        $instance->setOptions($options);
    }
    return $instance;
}
