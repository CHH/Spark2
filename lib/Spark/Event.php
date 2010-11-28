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

class Event
{
	protected static $handlers = array();
	
	public static function observe($subject, $event, $handler)
	{
	}
	
	public static function trigger($subject, $event, $memo = null)
	{
	}
}
