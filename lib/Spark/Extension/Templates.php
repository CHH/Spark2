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

class Templates extends Base
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
        $template .= ".phtml";
        $template = $this->findTemplate($template);
        return $this->getEngine("\Spark\View\PhpEngine")->render($template, $view);
    }

    /**
     * Renders a template with the Phly_Mustache Template engine
     */
    function mustache($template, $view = null)
    {
        $template .= ".mustache";
        $template = $this->findTemplate($template);
        return $this->getEngine("\Spark\View\PhlyMustacheEngine")->render($template, $view);
    }

    function findTemplate($name)
    {
        $views = $this->app->settings->get("views");

        if (!is_array($views)) {
            $views = array($views);
        }

        foreach ($views as $path) {
            if (file_exists($path . '/' . $name)) {
                return $path . '/' . $name;
            }
        }

        throw new \UnexpectedValueException(
            "Template $name not found in Paths " . join($views, ", ")
        );
    }

    protected function getEngine($engine)
    {
        if (empty($this->engines[$engine])) {
            $this->engines[$engine] = new $engine;
        }
        return $this->engines[$engine];
    }
}
