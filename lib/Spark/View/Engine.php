<?php
/**
 * Interface for View Renderers
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

interface Engine
{
    function render($template, $view = null);
    function setTemplatePath($path);
}
