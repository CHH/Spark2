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

namespace Spark\Util;

use InvalidArgumentException,
    SplDoublyLinkedList,
    Spark\Http\Request,
    Spark\Util\ReturnValues;

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
        if (!is_callable($filter)) {
            throw new InvalidArgumentException("You must supply a valid Callback as Filter");
        }
        $this->filters->push($filter);
        return $this;
    }
    
    /**
     * Prepends a filter
     *
     * @param  callback $filter
     * @return HttpFilterChain
     */
    function prepend($filter)
    {   
        if (!is_callable($filter)) {
            throw new InvalidArgumentException("You must supply a valid Callback as Filter");
        }
        $this->filters->unshift($filter);
        return $this;
    }
    
    function filter(array $argv)
    {
        return $this->filterUntil($argv, function() {
            return false;
        });
    }
    
    /**
     * Allows a filter chain to be used as filter inside another filter chain
     *
     * @param array $argv Array of filter arguments
     */
    function __invoke(array $argv)
    {   
        return $this->filter($request);
    }
    
    /**
     * Executes the filters
     *
     * @param  array        $argv  Array of filter arguments
     * @param  callback     $until Loop breaks if TRUE is returned by the callback
     * @return SparkCore\Util\ReturnValues Collection of filter return values
     */
    function filterUntil(array $argv, $until)
    {
        if (!is_callable($until)) {
            throw new InvalidArgumentException("No valid callback given");
        }
        
        $return = new ReturnValues;

        foreach ($this->filters as $filter) {
            $return->push(call_user_func_array($filter, $argv));
            
            if (true === call_user_func_array($until, $argv)) {
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
