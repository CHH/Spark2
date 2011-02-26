<?php
/**
 * Provides configuration
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Core
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Extension {
    class Configuration
    {
        public $__export = array("set", "getOption", "getOptions");
        
        protected static $options = array();
        
        /**
         * Sets an option
         * 
         * @param  string|array $spec Either list of key-values or name of the key
         * @param  mixed $value
         * @return App
         */
	    function set($spec, $value = null)
	    {
	        if (is_array($spec)) {
	            foreach ($spec as $option => $value) {
	                static::$options[$option] = $value;
	            }
	            return $this;
	        }
	        static::$options[$spec] = $value;
	        return $this;
	    }

        function get($spec = null)
        {
            if (null === $spec) {
                return static::$options;
            }
            return $this->getOption($spec);
        }
        
        /**
         * Get an option
         *
         * @param  mixed $spec Returns the value of the option or all options if NULL
         * @return mixed
         */
        function getOption($key)
        {
            return !empty(static::$options[$key]) ? static::$options[$key] : null;
        }

        function getOptions()
        {
            return static::$options;
        }
    }

    \Spark::register(__NAMESPACE__ . "\Configuration");
}
