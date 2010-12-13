<?php
/*
 * Implementation of explicit Autoloading pattern (similar to Ruby's autoload())
 *
 * Every class which should be autoloaded must be registered by a call to
 * autoload(). This satisfies the needs for easy understandable class dependencies
 * and Lazy loading.
 */

function Spark()
{
    static $instance;
    return null === $instance ? $instance = new Spark() : $instance;
}

class Spark 
{
    protected $autoloadable = array();
    
    function autoloader($symbol)
    {
        if (!isset($this->autoloadable[$symbol])) {
            return false;
        }
        require($this->autoloadable[$symbol]);
    }
    
    function autoload($symbol, $fromFile)
    {
        $this->autoloadable[$symbol] = $fromFile;
        return $this;
    }
}

spl_autoload_register(array(Spark(), "autoloader"));

/**
 * Declares a class or interface as autoloadable and registers it in the 
 * autoload class map
 *
 * @param  string $symbol  Name of the class or interface
 * @param  string $require File which should get required if the Symbol is used
 * @return void
 */
function autoload($symbol, $require)
{
    Spark()->autoload($symbol, $require);
}

/*
 * Default includes
 */
autoload('Spark\Exception', __DIR__ . '/Spark/Exception.php');
autoload('Spark\Options',   __DIR__ . '/Spark/Options.php');

require('Spark/Util.php');
require('Spark/App.php');
