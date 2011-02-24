<?php
/**
 * A class which represents the incoming HTTP Request
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
namespace Spark\Http;

use Symfony\Component\HttpFoundation;

class Request extends HttpFoundation\Request
{
    function param($key)
    {
        return $this->get($key);
    }
}
