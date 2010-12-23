<?php
/**
 * Front Controller
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Controller
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

require_once('Util.php');

autoload('Spark\Controller\Exception', __DIR__ . '/Controller/Exception.php');

require_once('Controller/Controller.php');
require_once('Controller/ActionController.php');
require_once('Controller/Resolver.php');
require_once('Controller/StandardResolver.php');
