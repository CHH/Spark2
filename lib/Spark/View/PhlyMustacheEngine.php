<?php
/**
 * A View Renderer which uses Phly_Mustache
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_View
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\View;

if (!class_exists("\Phly\Mustache\Mustache")) {
    throw new \RuntimeException("Phly_Mustache could not be found. Make sure it's installed");
}

use Phly\Mustache\Mustache,
    Spark\Http\Response;

class PhlyMustacheEngine implements Engine
{
    /** @var Mustache */
    protected $mustache;  
    
    function render($template, $view = null)
    {
        $mustache = $this->getMustache();
        
        return new Response($mustache->render($template, $view));
    }
    
    function setMustache(Mustache $mustache)
    {
        $this->mustache = $mustache;
        return $this;
    }
    
    function getMustache()
    {
        if (null === $this->mustache) {
            $this->mustache = new Mustache;
        }
        return $this->mustache;
    }
}
