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

	/**
	 * Gets called by the Front Controller on dispatch
	 *
	 * @param  \Spark\Controller\HttpRequest  $request
	 * @param  \Spark\Controller\HttpResponse $response
	 * @return void
	 */
	public function __invoke(HttpRequest $request, HttpResponse $response)
	{
		$this->beforeFilter($request, $response);

		$action = $request->getActionName();

		if($action == null) {
			$action = "index";
		}

		$this->request = $request;
		$this->response = $response;

		$method = str_replace(" ", "", ucwords(str_replace("_", " ", str_replace("-", " ", strtolower($action)))));
		$method[0] = strtolower($method[0]);

		$method = $action . "Action";

		if(!method_exists($this, $method)) {
			$controller = get_class($this);
			throw new Exception("The action {$action} was not found in
				the controller {$controller}. Please make sure the method {$method} exists.", 404);
		}
		
		$this->$method($request, $response);
		$this->afterFilter($request, $response);
	}

	public function beforeFilter($request, $response)
	{}
	
	public function afterFilter($request, $response)
	{}
	
	protected function forward(\Zend_Controller_Request_Abstract $request)
	{
		$request->setDispatched(false);
	}
}
