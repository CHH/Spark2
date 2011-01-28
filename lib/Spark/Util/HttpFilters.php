<?php

namespace Spark\Util;

use InvalidArgumentException,
    SplQueue,
    SplDoublyLinkedList,
    Spark\HttpRequest,
    Spark\HttpResponse;

class HttpFilters implements \IteratorAggregate
{
    /** @var SplQueue */
    protected $filters;
    
    /** @var callback */    
    protected $filterUntil;
    
    function __construct()
    {
        $this->filters = new SplQueue;
    }
    
    /**
     * Adds a filter
     *
     * Filters should be a function of HttpRequest and HttpResponse.
     *
     * @param  callback $filter
     * @return HttpFilters
     */
    function queue($filter)
    {
        if (!is_callable($filter)) {
            throw new InvalidArgumentException("You must supply a valid Callback as Filter");
        }
        if (is_array($filter) or is_string($filter)) {
            $filter = function(HttpRequest $request, HttpResponse $response) use ($filter) {
                return call_user_func($filter, $request, $response);
            };
        }
        $this->filters->enqueue($filter);
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
    
    function __invoke(HttpRequest $request, HttpResponse $response)
    {   
        return $this->filter($request, $response);
    }
    
    /**
     * Executes the filters
     *
     * @param  HttpRequest  $request
     * @param  HttpResponse $response
     * @return ArrayObject  Array of each filter's return value
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
