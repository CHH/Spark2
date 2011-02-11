<?php

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
