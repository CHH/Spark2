<?php
/*
 * Implementation of explicit Autoloading pattern (similar to Ruby's autoload())
 *
 * Every class which should be autoloaded must be registered by a call to
 * autoload(). This satisfies the needs for easy understandable class dependencies
 * and Lazy loading.
 *
 * @category Spark
 * @package  Spark_Util
 */
namespace Spark\Util;

/**
 * Stores and returns a single instance of the class loader
 *
 * @return ClassLoader
 */
function ClassLoader()
{
	static $instance;
	return null === $instance ? $instance = new ClassLoader : $instance;
}

/**
 * Class loader
 *
 * Provides a registry for the explicit autoloading pattern
 *
 * @category Spark
 * @package  Spark_Util
 */
class ClassLoader 
{
	protected $autoloadable = array();

	function __invoke($symbol)
	{
		if (!isset($this->autoloadable[$symbol])) {
	        return false;
	    }
	    require($this->autoloadable[$symbol]);
	}
	
	function register()
	{
	    spl_autoload_register($this);
	    return $this;
	}
	
	function unregister()
	{
		spl_autoload_unregister($this);
	}
	
	function registerSymbol($symbol, $fromFile)
	{
	    $this->autoloadable[$symbol] = $fromFile;
	    return $this;
	}
}
