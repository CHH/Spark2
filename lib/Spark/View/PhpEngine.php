<?php
/**
 * A very simple View Engine which uses PHP
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

use SplStack,
    Spark\Http\Response;

class PhpEngine implements Engine
{
    /** @var SplStack */
    protected $templatePaths;    
    
    function __construct()
    {
        $this->templatePaths = new SplStack;
    }
    
    /**
     * Renders the given template with the given view
     *
     * @param  string $template Name of the template, Suffix .phtml gets appended
     * @param  mixed  $view Iteratable variable
     * @return Response
     */
    function render($template, $view = null)
    {
        foreach ($view as $var => $value) {
            $this->{$var} = $value;
        }
        
        $file = $this->findTemplate($template);
        
        if (!$file) {
            throw new \Exception(sprintf(
                "Template %s.phtml not found in paths %s",
                $template, join(iterator_to_array($this->templatePaths), ", ")
            ));
        }
        
        ob_start();
        include($file);
        $content = ob_get_clean();
        
        return new Response($content);
    }
    
    /**
     * Adds a template path to the stack
     *
     * @param string $path
     */
    function setTemplatePath($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException("Path $path is not valid");
        }
        $this->templatePaths->push($path);
        return $this;
    }
    
    /**
     * Searches the template paths and returns the full path to the template file
     *
     * @param  string $template
     * @return string Filename
     */
    protected function findTemplate($template)
    {
        foreach ($this->templatePaths as $path) {
            if (is_readable($file = $path . "/" . $template . ".phtml")) {
                return $file;
            }
        }
        return false;
    }
}
