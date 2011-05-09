<?php
/**
 * Enables some settings for using the Spark\Base Instance directly
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
namespace Spark;

class Application extends Base
{
    function __construct()
    {
        parent::__construct();
        
        $settings = $this->settings;
        
        // Enable response sending after the dispatching is done
        $settings->enable("send_response");
        
        // Determine the default directories based on the app_file
        $appFile = dirname($settings->get("app_file")) ?: getcwd();
        $settings->set("views", $appFile . "/views");
    }
}
