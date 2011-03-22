<?php

namespace Spark\Helper;

class Base extends \Spark\Extension\Base
{
    function request()
    {
        return $this->application()->request();
    }
}

