<?php
/**
 * Basic Command Interface
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Controller
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
interface Controller
{
    public function __invoke(
        \Zend_Controller_Request_Abstract  $request,
        \Zend_Controller_Response_Abstract $response
    );
}
