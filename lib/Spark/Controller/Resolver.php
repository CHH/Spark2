<?php
/**
 * Interface for the Command Resolver used by the Front Controller
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

use SparkCore\Request;

/**
 * @category   Spark
 * @package    Spark_Controller
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */
interface Resolver
{
    /**
     * Sets options
     *
     * @param  array $options Key value pairs
     * @return Resolver
     */
    function setOptions(Array $options);
    
    /**
     * Should resolve the request to a valid instance of CommandInterface
     *
     * @param  Spark_HttpRequest $request The routed Request
     * @return Spark_Controller_Controller
     */
    function getInstance(Request $request);
}
