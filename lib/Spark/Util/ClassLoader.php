<?php
/*
 * Implementation of explicit Autoloading pattern (similar to Ruby's autoload())
 *
 * Every class which should be autoloaded must be registered by a call to
 * Classloader()->registerSymbol(), which gets usually done by the autoload() function
 * defined in the Util Package.
 * 
 * This satisfies the needs for easy understandable class dependencies
 * and Lazy loading.
 *
 * @category  Spark
 * @package   Spark_Util
 * @author    Christoph Hochstrasser
 * @copyright (c) 2011 Christoph Hochstrasser
 * @license   MIT License
 */
namespace Spark\Util;

/**
 * Stores and returns a single instance of the class loader
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
    protected $isRegistered = false;

	function __invoke($symbol)
	{
		if (!isset($this->autoloadable[$symbol])) {
	        return false;
	    }
	    if (!$file = realpath($this->autoloadable[$symbol])) {
	        throw new \UnexpectedValueException("$file does not exist");
	    }
	    require($file);
	}
	
	function register()
	{
	    spl_autoload_register($this);
	    $this->isRegistered = true;
	    return $this;
	}
	
	function unregister()
	{
	    $this->isRegistered = false;
		spl_autoload_unregister($this);
		return $this;
	}
	
	function isRegistered()
	{
	    return $this->isRegistered;
	}
	
	function registerSymbol($symbol, $fromFile)
	{
	    $this->autoloadable[$symbol] = $fromFile;
	    return $this;
	}
}
