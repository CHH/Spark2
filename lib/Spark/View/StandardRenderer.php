<?php
/**
 * The Standard View Renderer which uses PHP as Templating engine
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

class StandardRenderer implements Renderer
{
    protected $templatePaths;    
    
    function __construct()
    {
        $this->templatePaths = new SplStack;
    }
    
    function __invoke($template, $view = null)
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
    
    function setTemplatePath($path)
    {
        $this->templatePaths->push($path);
        return $this;
    }
    
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
