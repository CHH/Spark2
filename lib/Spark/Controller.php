<?php
/**
 * Controller Package
 *
 * Enables an MVC style workflow
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Controller
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2011 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

require_once('App.php');
require_once('Util.php');

autoload('Spark\Controller\Exception',      __DIR__ . '/Controller/Exception.php');
autoload('Spark\Controller\CallbackFilter', __DIR__ . '/Controller/CallbackFilter.php');

require_once('Controller/Controller.php');
require_once('Controller/ActionController.php');
require_once('Controller/Resolver.php');
require_once('Controller/StandardResolver.php');

/**
 * Facade for enabling the controller workflow
 *
 * @category Spark
 * @package  Spark_Controller
 */
class Controller
{
    /**
     * Enables controllers in the given App
     * 
     * Sets up the filter for controller callbacks, takes resolver options from
     * the app's options and attaches the filter.
     *
     * @param  App $app The App on which controllers should be enabled
     * @return App
     */
    static function enable(App $app)
    {
        $filter  = new Controller\CallbackFilter;
        $filter->getResolver()->setOptions($app->get());

        return $app->before($filter);
    }
}
