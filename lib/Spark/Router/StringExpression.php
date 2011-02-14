<?php
/**
 * Class for String Expressions
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Router
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Router;

class StringExpression
{
    const VAR_REGEX = "/\:([a-zA-Z0-9\_\-]+)/";    
    
    /** @var string */
    protected $expression;
    
    protected $requirements = array();
    
    /** @var string */
    protected $compiled;
    
    /**
     * Constructor
     *
     * @param string $expression
     * @param array  $requirements
     */
    function __construct($expression, array $requirements = array())
    {
        $this->expression   = (string) $expression;
        $this->requirements = $requirements;
    }
    
    /**
     * @return string
     */
    function __toString()
    {
        return $this->compile();
    }
    
    /**
     * @return string
     */
    function toRegExp()
    {
        return $this->compile();
    }
    
    /**
     * Compiles the expression
     *
     * @return string
     */
    protected function compile()
    {
        if (!empty($this->compiled)) {
            return $this->compiled;
        }
        
        $exp = $this->expression;
        $requirements = $this->requirements;
        
        $regex = preg_replace_callback(
            self::VAR_REGEX, 
            function($matches) use ($requirements) {
                $var = $matches[1];
                
                $subpattern = empty($requirements[$var]) 
                    ? "[a-zA-z0-9\-\_]+" 
                    : $requirements[$var];
                
                return "(?P<$var>$subpattern)";
            }, 
            $exp
        );
        
        $this->compiled = "#^" . $regex . "$#";
        return $this->compiled;
    }
}
