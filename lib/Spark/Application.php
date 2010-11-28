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

namespace Spark;
use Spark\Router\SimpleRouter as Router;

class Application
{
	public $routes;
	
	public function __construct()
	{
		$this->routes = new Router();
	}
	
	public function __invoke(
		\Zend_Controller_Request_Abstract  $request, 
		\Zend_Controller_Response_Abstract $response
	)
	{
		// trigger route matching and handle request
	}
}
