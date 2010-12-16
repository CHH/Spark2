<?php
/**
 * Simple and static implementation of the Event-Dispatcher pattern,
 * inspired by Prototype.js
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Controller
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

require_once "Event/Handler.php";

use SplQueue;

class Event
{
	protected static $handlers = array();
	
	static function observe($subject, $event, $callback = null)
	{
	    $key     = static::key($subject);
	    $handler = new Event\Handler($event, $subject, $callback);
	    
	    if (!isset(static::$handlers[$key])) {
	        static::$handlers[$key] = new SplQueue;
	    }
	    static::$handlers[$key]->enqueue($handler);
	}
	
	static function trigger($subject, $event)
	{
	    $key    = static::key($subject);
	    $return = null;
	    $memos  = array_slice(func_get_args(), 2);
        
	    foreach (static::$handlers[$key] as $handler) {
	        $return = $handler($event, $memos);
	        
	        if (false === $return) break;
	    }
	    
	    return $return;
	}
	
	protected static function key($input)
	{
	    if (is_object($input)) {
	        return spl_object_hash($input);
	    } else if (is_string($input) and !empty($input)) {
	        return $input;
	    } else {
	        throw new \InvalidArgumentException("Input must be a object or string");
	    }
	}
}
