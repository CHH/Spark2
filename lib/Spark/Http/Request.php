<?php
/**
 * A class which represents the incoming HTTP Request
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Http
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */
namespace Spark\Http;

use Spark\Helpers as helper,
    Symfony\Component\HttpFoundation;

class Request extends HttpFoundation\Request implements \ArrayAccess
{
    /**
     * @see Spark\Helpers\with_user_agent()
     */
    function withUserAgent($pattern, $callback)
    {
        return helper\with_user_agent($this, $pattern, $callback);
    }

    /**
     * @see Spark\Helpers\with_hostname()
     */
    function withHostName($pattern, $callback)
    {
        return helper\with_hostname($this, $pattern, $callback);
    }

    /**
     * @see Spark\Helpers\with_format()
     */
    function withFormat($formats, $callback)
    {
        return helper\with_format($this, $formats, $callback);
    }

    function offsetGet($offset)
    {
            return $this->get($offset);
    }
    
    function offsetSet($offset, $value)
    {
            throw new \BadMethodCallException("Params are read-only");
    }
    
    function offsetExists($offset)
    {
            return (bool) $this->get($offset);
    }
    
    function offsetUnset($offset)
    {
            throw new \BadMethodCallException("Params are read-only");
    }
}
