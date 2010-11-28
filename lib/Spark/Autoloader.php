<?php
/**
 * Simple Autoloader for loading classes from an include path
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

class Autoloader
{
    protected $includePath;
    
    const PREFIX_SEPARATOR    = "_";
    const NAMESPACE_SEPARATOR = "\\";    
    
    protected $suffix = ".php";
    
    /**
     * Constructor
     *
     * @param  array $options key => value pairs, get inflected to Setter names
     * @return Autoloader
     */
    public function __construct(Array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Set Options on this Instance
     *
     * @param  array $options
     * @return Autoloader
     */
    public function setOptions(Array $options)
    {
        Options::setOptions($this, $options);
        return $this;
    }
	
    /**
     * Loads the given class
     *
     * @param  string $class
     * @return false|mixed False if file is not accessible, otherwise the return value
     *                     of the included file
     */
    public function autoload($class)
    {
        $filename = str_replace(
            array(self::PREFIX_SEPARATOR, self::NAMESPACE_SEPARATOR), 
            DIRECTORY_SEPARATOR, 
            $class
        );
        
        if ($this->includePath) {
            $filename = $this->includePath . DIRECTORY_SEPARATOR . $filename;
        }
        $filename .= $this->suffix;
		$filename = realpath($filename);
		
		if (false === $filename) {
			return false;
		}
        require_once $filename;
    }
    
    /**
     * Adds this autoloader on the autoloader stack
     *
     * @return Autoloader
     */
    public function register()
    {
        spl_autoload_register(array($this, "autoload"));
        return $this;
    }

    /**
     * Removes this autoloader from the autoloader stack
     *
     * @param Autoloader
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, "autoload"));
        return $this;
    }
    
    /**
     * Sets the script suffix, defaults to ".php"
     *
     * @param  string $suffix
     * @return Autoloader
     */
    public function setSuffix($suffix)
    {
        if (!is_string($suffix) or empty($suffix)) {
            throw new InvalidArgumentException(sprintf(
                "Suffix must be a string, %s given",
                gettype($suffix)
            ));
        }
        $this->suffix = $suffix;
        return $this;
    }
    
    /**
     * Returns the script suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }
    
    /**
     * Sets the base path for the case no Prefix => Path mapping is available for 
     * the class to load. If a relative path is set, then the include_path gets searched
     *
     * @param  string $path;
     * @return Autoloader
     */
    public function setIncludePath($path)
    {
        $this->includePath = $path;
        return $this;
    }
}
