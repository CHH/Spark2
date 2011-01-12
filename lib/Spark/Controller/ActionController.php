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
    Spark\Util;

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
    final function __invoke(HttpRequest $request, HttpResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
        
        $this->before($request, $response);
        
        $action = $request->getMetadata("action");

        if($action == null) {
            $action = "index";
        }
        
        $method = Util\str_camelize($action, false) . "Action";

        if(!is_callable(array($this, $method))) {
            throw new Exception(sprintf(
                "The action %s was not found in the controller %s. Please make sure the method %s exists.",
                $action, get_class($this), $method
            ), 404);
        }
        
        $this->{$method}($request, $response);
        $this->after($request, $response);
    }
    
    function before(HttpRequest $request, HttpResponse $response)
    {}

    function after(HttpRequest $request, HttpResponse $response)
    {}
}
