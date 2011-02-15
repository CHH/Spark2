<?php
/**
 * Holds values and provides access to the first and last values
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

namespace Spark\Util;

class ReturnValues extends \SplStack
{
    function first()
    {
        if (0 === $this->count()) {
            return null;
        }
        return parent::top();
    }

    function last()
    {
        return parent::bottom();
    }

    function contains($value)
    {
        foreach ($this as $item) {
            if ($item === $value) {
                return true;
            }
        }
        return false;
    }
}
