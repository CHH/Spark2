<?php
/**
 * Application base class, facade for controller and router
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_App
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

require_once('Util.php');
require_once("HttpRequest.php");
require_once("HttpResponse.php");
require_once("Router.php");

use SplStack,
    Spark\HttpRequest, 
    Spark\HttpResponse,
    Spark\Util\Options;

class App
{
    /** @var Spark\Router */
	public $routes;
    
	/** @var SplStack */
	protected $filters;
	
	function __construct(Array $options = array())
	{
	    if ($options) {
	        $this->setOptions();
	    }

        $this->routes  = new Router;
	    $this->filters = new SplStack;
	}
	
	function setOptions(Array $options = array())
	{
	    Options::setOptions($this, $options);
	    return $this;
	}
	
	function __invoke(HttpRequest $request, HttpResponse $response)
	{
	    ob_start();
	    
	    try {
	        $callback = $this->routes->route($request);
	        $callback($request, $response);
		} catch (\Exception $e) {
		    $response->addException($e);
		}
		
		$response->append(ob_get_clean());
		
		foreach ($this->filters as $filter) {
		    $filter($request, $response);
		}
		
		$response->send();
	}
	
	function postDispatch($filter)
	{
	    $this->filters->push($filter);
	    return $this;
	}
}
