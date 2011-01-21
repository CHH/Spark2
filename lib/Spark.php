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

function set($option, $value)
{
    Spark\App()->set($option, $value);
}

function before($callback)
{
    Spark\App()->before($callback);
}

function after($callback)
{
    Spark\App()->after($callback);
}

function match($routeSpec, $callback)
{
    Spark\App()->route()->match($routeSpec, $callback);
}

function get($routeSpec, $callback = null)
{
    Spark\App()->route()->get($routeSpec, $callback);
}

function post($routeSpec, $callback = null)
{
    Spark\App()->route()->post($routeSpec, $callback);
}

function put($routeSpec, $callback = null)
{
    Spark\App()->route()->put($routeSpec, $callback);
}

function delete($routeSpec, $callback = null)
{
    Spark\App()->route()->delete($routeSpec, $callback);
}

function error($class, $callback = null)
{
    Spark\App()->error($class, $callback);
}

function not_found($callback)
{
    Spark\App()->notFound($callback);
}

function handle_request()
{
    $app = Spark\App();
    return $app(new Spark\HttpRequest, new Spark\HttpResponse);
}
