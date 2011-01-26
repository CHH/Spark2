<?php
/**
 * Basic Implementation of an Action Controller. 
 *
 * Allows to request methods directly from an url, e.g. a request to the 
 * action "foo" gets delegated to a Method called fooAction().
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

use Spark\HttpRequest, 
    Spark\HttpResponse,
    Spark\Util,
    Spark\Router\Redirect,
    InvalidArgumentException;

/**
 * @category   Spark
 * @package    Spark_Controller
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */
abstract class ActionController implements Controller
{
    /** @var ActionController */
    protected static $instance;
    
    final function __construct()
    {
        $this->init();
    }
    
    /**
     * Template method, can be used to setup common resources
     */
    function init()
    {}
    
    /**
     * Gets called by the Front Controller on dispatch
     *
     * @param  \Spark\Controller\HttpRequest  $request
     * @param  \Spark\Controller\HttpResponse $response
     * @return void
     */
    final function __invoke(HttpRequest $request, HttpResponse $response)
    {
        $this->before($request, $response);
        
        // Store instances to allow helper methods to access them
        $this->request  = $request;
        $this->response = $response;
        
        $action = $request->meta("action");
        
        if ($action == null) {
            $action = "index";
        }
        
        $method = Util\str_camelize($action, false) . "Action";

        if (!is_callable(array($this, $method))) {
            throw new Exception(sprintf(
                "The action %s was not found in the controller %s. "
                . "Please make sure the method %s exists.",
                $action, get_class($this), $method
            ), 404);
        }
        
        $this->{$method}($request, $response);
        $this->after($request, $response);
    }
    
    /**
     * Template Method, run before an action is dispatched
     */
    function before(HttpRequest $request, HttpResponse $response)
    {}

    /**
     * Template Method, ran after an action was dispatched
     */
    function after(HttpRequest $request, HttpResponse $response)
    {}
    
    protected function redirect($url)
    {
        if (empty($url)) {
            throw new InvalidArgumentException("URL cannot be an empty string");
        }
        $redirect = new Redirect($url);
        $redirect($this->request, $this->response);
    }
    
    /**
     * Returns a callback which calls the given action and is compatible with 
     * request callbacks.
     *
     * Can be used to directly attach an action to a route, e.g.
     * $router->match(array("/foo/bar" => FooController::action("bar")));
     *
     * This omits the need for the Resolver in simple Applications.
     *
     * @param  string  $actionName
     * @return Closure
     */
    final static function action($actionName)
    {
        if (!is_string($actionName) or empty($actionName)) {
            throw new InvalidArgumentException("Action name must be a valid string");
        }
        
        $instance = static::getInstance();
        $method   = Util\str_camelize($actionName, false) . "Action";
        
        if (!is_callable(array($instance, $method))) {
            throw new InvalidArgumentException("Controller has no action named \"$actionName\"");
        }
        
        $callback = 
            function(HttpRequest $request, HttpResponse $response) 
                use ($instance, $method) 
            {
                return $instance->{$method}($request, $response);
            };
        
        return $callback;
    }
    
    /** 
     * Returns an instance of the controller
     *
     * @return ActionController
     */
    protected static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }
}
