<?php
/**
 * Basic Implementation of an Action Controller. Allows to request methods directly
 * from an url, e.g. a request to the action "foo" gets delegated to a Method
 * called fooAction().
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

namespace Spark\Controller;

/**
 * @category   Spark
 * @package    Spark_Controller
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */
abstract class ActionController implements Controller
{
    protected $request;
    protected $response;
    
    final function __construct()
    {
        $this->init();
    }
    
    function init()
    {}
    
    /**
     * Gets called by the Front Controller on dispatch
     *
     * @param  \Spark\Controller\HttpRequest  $request
     * @param  \Spark\Controller\HttpResponse $response
     * @return void
     */
    function __invoke(HttpRequest $request, HttpResponse $response)
    {
        $this->before($request, $response);

        $action = $request->getActionName();

        if($action == null) {
            $action = "index";
        }

        $this->request = $request;
        $this->response = $response;

        $method = str_camelize($action, false) . "Action";

        if(!method_exists($this, $method)) {
            $controller = get_class($this);
            throw new Exception(sprintf(
                "The action %s was not found in the controller %s. Please make sure the method %s exists.",
                $action, get_class($this), $method
            ), 404);
        }

        $this->{$method}($request, $response);
        $this->after($request, $response);
    }
    
    function before($request, $response)
    {}

    function after($request, $response)
    {}
}
