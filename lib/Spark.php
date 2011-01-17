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
require_once('Spark/Util.php');

/*
 * Default includes
 */
autoload('Spark\Exception', __DIR__ . '/Spark/Exception.php');

require_once('Spark/Controller.php');
require_once('Spark/HttpRequest.php');
require_once('Spark/HttpResponse.php');
require_once('Spark/Router.php');
require_once('Spark/App.php');
