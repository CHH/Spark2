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
        
        if (!file_exists($template)) {
            throw new \Exception(sprintf(
                "Template %s.phtml not found in paths %s",
                $template, join(iterator_to_array($this->templatePaths), ", ")
            ));
        }
        
        $content = $this->getTemplateContent($template);
        
        return new Response($content);
    }

    protected function getTemplateContent($template)
    {
        ob_start();
        include($template);
        $content = ob_get_clean();

        return $content;
    }
}
