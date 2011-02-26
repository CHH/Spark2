<?php
/**
 * A generic Filter chain implementation
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
    Spark\Util\ReturnValues;

class FilterChain implements \IteratorAggregate, \Countable
{
    /** @var SplQueue */
    protected $filters;
    
    function __construct(array $filters = array())
    {
        $this->filters = new SplDoublyLinkedList;

        if (!empty($filters)) {
            array_walk($filters, array($this, "add"));
        }
    }
    
    /**
     * Appends a filter
     *
     * Filter should be a function of Http\Request and return an Http\Response.
     *
     * @param  callback $filter
     * @return HttpFilterChain
     */
    function add($filter)
    {
        if (!is_callable($filter)) {
            throw new InvalidArgumentException("You must supply a valid Callback as Filter");
        }
        $this->filters->push($filter);
        return $this;
    }
    
    /**
     * Notifies all filters
     *
     * @param  array $argv Value which should be passed to the filters
     * @return ReturnValues
     */
    function filter(array $argv)
    {
        return $this->filterUntil($argv, function() {
            return false;
        });
    }
    
    /**
     * Allows a filter chain to be used as filter inside another filter chain
     *
     * @param mixed $arg,... Array of filter arguments
     */
    function __invoke($arg)
    {   
        return $this->filter(func_get_args());
    }
    
    /**
     * Notifies all filters until the supplied callback returns true
     *
     * @param  array    $argv  Array of filter arguments
     * @param  callback $until Loop breaks if TRUE is returned by the callback
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

    function isEmpty()
    {
        return 0 === $this->count();
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
