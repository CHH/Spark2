<?php
/**
 * Simple class for compiling string expressions
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Util
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Util;

class StringExpression
{
    const VAR_REGEX = "/\:([a-zA-Z0-9\_\-]+)/";    
    
    /** @var string */
    protected $expression;
    
    protected $requirements = array();
    
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
    function toRegExp($delimiters = true)
    {
        return $this->compile($delimiters);
    }
    
    /**
     * Compiles the expression
     *
     * @return string
     */
    protected function compile($delimiters = true)
    {
        $exp = $this->expression;
        $requirements = $this->requirements;
        
        $exp = str_replace('/', '\/', $exp);
        
        $regex = preg_replace_callback(
            self::VAR_REGEX, 
            function($matches) use ($requirements) {
                $var = $matches[1];
                
                $subpattern = empty($requirements[$var]) 
                    ? "[a-zA-Z0-9\-\_]+" 
                    : $requirements[$var];
                
                return "(?<$var>$subpattern)";
            }, 
            $exp
        );
        
        if ($delimiters) {
            $regex = "#^" . $regex . "$#";
        }
        return $regex;
    }
}
