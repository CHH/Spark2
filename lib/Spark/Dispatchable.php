<?php
/**
 * A simple interface for all kinds of reusable Web Applications
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

use Spark\Http\Request,
    Spark\Http\Response;

interface Dispatchable
{
    /**
     * @param  Request  $request  The Request to the Application
     * @param  Response $previous The response of the previous handler, if any
     * @return Response
     */
    function __invoke(Request $request, Response $previous = null);
}
