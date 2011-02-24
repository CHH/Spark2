<?php
/**
 * Http Package
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Http
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

autoload("Spark\Http\Exception",         __DIR__ . "/Http/Exception.php");
autoload("Spark\Http\NotFoundException", __DIR__ . "/Http/NotFoundException.php");

require_once "Http/Request.php";
require_once "Http/Response.php";
