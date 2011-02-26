<?php
/**
 * Standard Route, which can also match by method
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Router
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Router;

use InvalidArgumentException,
    Spark\Util,
    Spark\Http\Request,
    Spark\Router\StringExpression;

class StandardRoute implements Route
{
    /** @var string HTTP Method which this route should be bound to */
    protected $method;

    /** @var string Raw route string */
    protected $route;

    protected $constraints = array();
    
    /** @var array Additional metadata associated with this route */
    protected $defaults = array();

    /** @var string */
    protected $urlDelimiter = "/";

    static function create($route)
    {
        return new static($route);
    }
    
    /**
     * Constructor
     *
     * @param  string $route
     * @return RestRoute
     */
    function __construct($route)
    {
        $this->route = rtrim($route, "/");
    }
    
    function __invoke(Request $request)
    {
        if (null !== $this->method and $request->getMethod() !== $this->method) {
            return false;
        }
        
        $requestUri = rtrim($request->getRequestUri(), $this->urlDelimiter);
        
        $exp = new StringExpression($this->route, $this->constraints);
        $result = preg_match_all($exp->toRegExp(), $requestUri, $matches);
        
        if (!$result) {
            return false;
        }
        $params = array();
        
        foreach ($matches as $param => $value) {
            $value = current($value);
            if (null == $value) {
                continue;
            }
            $params[$param] = $value;
        }
        
        $params = array_merge($this->defaults, $params);
        
        foreach ($params as $key => $value) {
            $request->attributes->set($key, $value);
        }
        return true;
    }

    /** 
     * Assign default values for route parameters
     */
    function defaults(array $defaults)
    {
        $this->defaults = array_merge($this->defaults, $defaults);
        return $this;
    }
    
    function method($httpMethod = null)
    {
        $this->method = empty($httpMethod) ? null : strtoupper($httpMethod);
        return $this;
    }
    
    /**
     * Add constraints to params
     *
     * @param  array|string $spec       Either list of param-constraint pairs or name of param
     * @param  string       $constraint Regular Expression
     * @return RestRoute
     */
    function constraints(array $constraints)
    {
        $this->constraints = array_merge($this->constraints, $constraints);
        return $this;
    }
}
