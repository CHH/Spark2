<?php
/**
 * Spark Framework
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Core
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */
require_once('Spark/App.php');

use Spark\App,
    Spark\HttpRequest,
    Spark\HttpResponse;

function Spark(App $app = null)
{
    static $instance;
    
    if (null === $instance) {
        if (null !== $app) {
            $instance = $app;
        } else {
            $instance = new App;
        }
    }
    return $instance;
}

class Spark
{
    static function run($app = null)
    {
        if (null === $app) {
            $app = Spark();
        }
        
        // Class Name given
        if (is_string($app)) {
            $app = new $app;
        }
        
        if (!$app instanceof App) {
            throw new InvalidArgumentException("App must be a valid instance of Spark\App");
        }
        
        $request  = new HttpRequest;
        $response = new HttpResponse;
        
        return $app($request, $response);
    }
}
