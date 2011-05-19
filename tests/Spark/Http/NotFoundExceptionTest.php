<?php

namespace Spark\Test\Http;

use Spark\Http\NotFoundException;

class NotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    function testHasCode404()
    {
        $e = new NotFoundException;
        $this->assertEquals(404, $e->getCode());
    }
}
