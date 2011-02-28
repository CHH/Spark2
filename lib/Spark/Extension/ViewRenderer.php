<?php
/**
 * View renderer Extension
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

namespace Spark\Extension;

class ViewRenderer extends Base
{
    /**
     * View Engine instances
     */
    protected $engines = array();

    /*
     * Methods which get exported to the DSL
     */

    /**
     * Renders a template using PHP as template engine
     */
    function phtml($template, $view = null)
    {
        return $this->getEngine("\Spark\View\PhpEngine")->render($template, $view);
    }

    /**
     * Renders a template with the Phly_Mustache Template engine
     */
    function mustache($template, $view = null)
    {
        return $this->getEngine("\Spark\View\PhlyMustacheEngine")->render($template, $view);
    }
    
    protected function getEngine($engine)
    {
        if (empty($this->engines[$engine])) {
            $this->engines[$engine] = new $engine;
            
            $views = $this->context->settings()->get("views");
            
            $this->engines[$engine]->setTemplatePath($views);
        }
        return $this->engines[$engine];
    }
}
