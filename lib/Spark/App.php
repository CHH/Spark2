<?php
/**
 * Application base class, facade for controller and router
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

autoload('Spark\Event', realpath(__DIR__ . "Event.php"));

require_once("Controller.php");
require_once("Router.php");

namespace Spark;

class App
{
	public $routes;
	
	public function __construct()
	{
		$this->routes = new Router();
	}
	
	public function __invoke(
		Controller\HttpRequest  $request, 
		Controller\HttpResponse $response
	)
	{
		$callback = $this->routes->match($request);
		
		$callback($request, $response);
		
		$response->send();
	}
}
