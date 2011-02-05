<?php
/**
 * Simple Router Callback which does a redirect to the specified location
 * 
 * @category Spark
 * @package  Spark_Router
 * @copyright (c) Christoph Hochstrasser
 * @license MIT License
 */

namespace Spark\Router;

use SparkCore\Request,
    SparkCore\Response;

/**
 * Simple Route Callback which does a redirect to the Location specified in the constructor
 */
class Redirect
{
    protected $location;
    protected $code;    
    
    function __construct($location, $code = 302)
    {
        $this->location = $location;
        $this->code = $code;
    }    
    
    function __invoke(Request $request, Response $response) 
    {
        $response->header("location", $this->location);
        $response->setCode($this->code);
    }
}
