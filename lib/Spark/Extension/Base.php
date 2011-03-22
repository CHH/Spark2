<?php
/**
 * Extension Base Class
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Util
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Extension;

class Base
{
    /**
     * Application this Extension runs in
     */
    protected $app;

    function exports()
    {
        $self = __CLASS__;

        return array_filter(get_class_methods($this), function($method) use ($self) {
            return substr($method, 0, 2) != "__" and !in_array($method, get_class_methods($self));
        });
    }

    function application()
    {
        return $this->app;
    }

    function setApplication(\Spark\App $app)
    {
        $this->app = $app;
        return $this;
    }
}

