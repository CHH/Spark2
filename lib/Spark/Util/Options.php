<?php
/**
 * Spark Framework
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Object
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Util;

/**
 * @category   Spark
 * @package    Spark_Object
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */
class Options 
{
	/**
	 * Calls the Setter Methods in the given object context for every key
	 * in the supplied options. The Name of the Setter Method must be camelCased
	 * and the key in the $options Array must have underscores  
	 * e.g. for the key "file_name" the Setter's name is "setFileName".
	 *
	 * @throws InvalidArgumentException If no object is given as context
	 * @param object $context The object context in which the Setters get called
	 * @param array  $options Array containing key => value pairs
	 * @param array  $settableOptions Optional list of fields which are settable 
  	 *                                on the object
  	 * @return bool  true if some options have been set in the context, false if no
 	 *               options were set
 	 */
	static public function setOptions($context, array $options, array $defaults = array())
	{
		if (!is_object($context)) {
			throw new \InvalidArgumentException("Context for setting options is not an Object");
		}
		if (!$options) {
			return false;
		}
		
		if ($defaults) {
			$options = array_merge($defaults, $options);
		}
		
		foreach ($options as $key => $value) {
			$setterName = self::_getSetterName($key);
			
			if (!is_callable(array($context, $setterName))) continue;
			else $context->{$setterName}($value);
		}
		return true;
	}
	
	/**
	 * Converts underscore_option_name to camelCasedSetterName
	 *
	 * @param  string $option The Name of the Option which should be converted
	 * @return string         The Name of the Setter Method
	 */
	static protected function _getSetterName($option) {
		return "set" . str_camelize($option);
	}
}
