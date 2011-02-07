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
    SparkCore\Http\Request,
    SparkCore\Util\ReturnValues;

class FilterChain implements \IteratorAggregate, \Countable
{
    /** @var SplQueue */
    protected $filters;
    
    function __construct()
    {
        $this->filters = new SplDoublyLinkedList;
    }
    
    /**
     * Appends a filter
     *
     * Filter should be a function of Http\Request and return an Http\Response.
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
            $filter = function(HttpRequest $request) use ($filter) {
                return call_user_func($filter, $request);
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
    
    function filter(Request $request)
    {
        return $this->filterUntil($request, function() {
            return false;
        });
    }
    
    /**
     * Allows a filter chain to be used as filter inside another filter chain
     *
     * @param HttpRequest  $request
     * @param HttpResponse $response
     */
    function __invoke(Request $request)
    {   
        $this->filter($request);
    }
    
    /**
     * Executes the filters
     *
     * @param  HttpRequest  $request
     * @param  callback     $until Loop breaks if TRUE is returned by the callback
     * @return SparkCore\Util\ReturnValues Collection of filter return values
     */
    function filterUntil(Request $request, $until)
    {
        if (!is_callable($until)) {
            throw new InvalidArgumentException("No valid callback given");
        }
        
        $return = new ReturnValues;

        foreach ($this->filters as $filter) {
            $return->push($filter($request));
            
            if (true === call_user_func($until, $request)) {
                break;
            }
        }
        return $return;
    }

    function count()
    {
        return $this->filters->count();
    }
    
    function getIterator()
    {
        return $this->filters;
    }
}
