<?php
/**
 * Standard Route
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

use InvalidArgumentException,
    Spark\Util,
    Spark\HttpRequest;

/**
 * TODO: Adapt regexes to enable optional params, ala "/users/(:id)"
 */
class RestRoute implements NamedRoute
{
    protected $name;    
    
    protected $method;
    protected $route;
    protected $regex;
    protected $metadata;
    protected $callback;    
    
    protected $urlDelimiter = "/";
    
    /**
     * Constructor
     *
     * The constructor takes an array of options as sole argument. The first element
     * in the array is treated as the route. If the index of the first element is a string, 
     * then the key is treated as route and the value as callback. Otherwise the
     * route is taken from the value of the first element.
     * 
     * Following elements in the array are treated as options. These include:
     *   - "to", callback for this route
     *   - "as", route name
     *   - "method", which HTTP method this route should match, if not given or NULL
     *     then the method is not considered
     *   - "root", Root path for the specified route, used for scoping
     *   - "metadata", Array of key value pairs, which is explicitly treated
     *     as metadata, use if a desired metadata key would collide with an 
     *     option value
     * 
     * All other elements than these defined options are stored and set as metadata
     * on the request if the route gets matched
     * 
     * @param  Array $routeSpec
     * @return RestRoute
     */
    function __construct(Array $routeSpec)
    {
        if (!$routeSpec) {
            throw new InvalidArgumentException("Route Spec cannot be empty.");
        }
        
        $route   = array_slice($routeSpec, 0, 1);
        $options = array_slice($routeSpec, 1) ?: array();
        
        // If first element of array is a $route => $callback pair
        if (is_string(key($route))) {
            $callback = current($route);
            $route    = key($route);
        } else {
            $route = current($route);
        }
        
        $callback = Util\array_delete_key("to", $options) ?: $callback;
        $name     = Util\array_delete_key("as", $options);
        $root     = Util\array_delete_key("root", $options);
        $metadata = Util\array_delete_key("meta", $options) ?: array();
        $method   = Util\array_delete_key("method", $options);
        
        $options["scope"] = trim($root, $this->urlDelimiter);
        
        $route = rtrim($root, $this->urlDelimiter) 
               . $this->urlDelimiter 
               . trim($route, $this->urlDelimiter);
        
        $this->route = rtrim($route, $this->urlDelimiter);
        
        $this->callback = $callback;
        $this->metadata = array_merge($options, $metadata);
        $this->name     = $name;
        $this->method   = (!empty($method)) ? strtoupper($method) : null;
        
        $this->parseStrExp();
    }

    function __invoke(HttpRequest $request)
    {
        if (null !== $this->method and $request->getMethod() !== $this->method) {
            return false;
        }
        
        $requestUri = rtrim($request->getRequestUri(), $this->urlDelimiter);
        
        $regex  = $this->regex;
        $result = preg_match_all($regex, $requestUri, $matches);
        
        if (!$result) {
            return false;
        }
        
        $meta = array();
        
        foreach ($matches as $param => $value) {
            $value = current($value);
            if (null == $value) {
                continue;
            }
            if (is_string($param)) {
                $meta[$param] = $value;
            }
        }
        
        $meta = array_merge($this->metadata, $meta);
        
        foreach ($meta as $key => $value) {
            $request->setMetadata($key, $value);
        }
        return $this->callback;
    }
    
    function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    function getName()
    {
        return $this->name;
    }
    
    function assemble(Array $params)
    {
        $url = $this->route;
        
        foreach ($params as $key => $value) {
            $url = str_replace(":$key", $value, $url);
        }
        
        return $url;
    }
    
    protected function parseStrExp()
    {
        $route    = $this->route;
        $alnum    = "[a-zA-Z0-9\_\-]";
        $pattern  = "/\:($alnum+)/";
        
        $namedCapture = "(?P<%s>%s)";
        
        $regex = preg_replace(
            $pattern, sprintf($namedCapture, "$1", ".+"), $route
        );
        $this->regex = "#^" . $regex . "$#";
    }
}
