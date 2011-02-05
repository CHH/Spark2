<?php
/**
 * Route Interface
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Router
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2011 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Router;

interface Route
{
    /**
     * Should match the given request and must return FALSE if the route has not
     * matched, or return callback if the route has matched
     *
     * @param  HttpRequest $request
     * @return mixed Callback
     */
    function __invoke(\SparkCore\Request $request);
}
