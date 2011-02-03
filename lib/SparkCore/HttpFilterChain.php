<?php
/**
 * Holds filters which filter request and response objects
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Util
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace SparkCore;

use InvalidArgumentException,
    SplDoublyLinkedList,
    SparkCore\HttpRequest,
    SparkCore\HttpResponse;

class HttpFilterChain implements \IteratorAggregate
{
    /** @var SplQueue */
    protected $filters;
    
    /** @var callback */    
    protected $filterUntil;
    
    function __construct()
    {
        $this->filters = new SplDoublyLinkedList;
    }
    
    /**
     * Appends a filter
     *
     * Filter should be a function of HttpRequest and HttpResponse.
     *
     * @param  callback $filter
     * @return HttpFilterChain
     */
    function append($filter)
    {
        return $this->add("bottom", $filter);
    }
    
    /**
     * Prepends a filter
     *
     * @param  callback $filter
     * @return HttpFilterChain
     */
    function prepend($filter)
    {
        return $this->add("top", $filter);
    }
    
    protected function add($position, $filter)
    {
        if (!is_callable($filter)) {
            throw new InvalidArgumentException("You must supply a valid Callback as Filter");
        }
        if (is_array($filter) or is_string($filter)) {
            $filter = function(HttpRequest $request, HttpResponse $response) use ($filter) {
                return call_user_func($filter, $request, $response);
            };
        }
        if ("bottom" === $position) {
            $this->filters->push($filter);
        } else if ("top" === $position) {
            $this->filters->unshift($filter);
        } else {
            throw new InvalidArgumentException("Invalid position $position, only "
                . "top and bottom are supported.");
        }
        return $this;
    }
    
    /**
     * Assigns a function which gets evaluated on each iteration.
     *
     * This function gets called with the HttpRequest and HttpResponse as arguments
     * and it causes the loop to break if the return value is TRUE
     *
     * @param  callback $filterUntil
     * @return HttpFilters
     */
    function until($filterUntil)
    {
        if (!is_callable($filterUntil)) {
            throw new InvalidArgumentException("The condition must be a valid callback");
        }
        $this->filterUntil = $filterUntil;
        return $this;
    }
    
    /**
     * Allows a filter chain to be used as filter inside another filter chain
     *
     * @param HttpRequest  $request
     * @param HttpResponse $response
     */
    function __invoke(HttpRequest $request, HttpResponse $response)
    {   
        return $this->filter($request, $response);
    }
    
    /**
     * Executes the filters
     *
     * @param  HttpRequest  $request
     * @param  HttpResponse $response
     * @return SplDoublyLinkedList Collection of filter return values
     */
    function filter(HttpRequest $request, HttpResponse $response)
    {
        $return = new SplDoublyLinkedList;
        
        foreach ($this->filters as $filter) {
            $return->push($filter($request, $response));
            
            if (null !== ($callback = $this->filterUntil)) {
                if (true === call_user_func($callback, $request, $response)) {
                    break;
                }
            }
        }
        return $return;
    }
    
    function getIterator()
    {
        return $this->filters;
    }
}
