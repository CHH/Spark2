<?php

namespace Spark\Test\Http;

use Spark\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    function testCanBeAccessedLikeAnArray()
    {
        $request = new Request;
        $request->query->set("foo", "bar");
        $request->request->set("bar", "baz");

        $this->assertEquals("bar", $request["foo"]);
        $this->assertEquals("baz", $request["bar"]);

        $this->assertTrue(isset($request["foo"]));
    }

    function testIssetReturnsFalseIfParamIsNotSet()
    {
        $request = new Request;
        $request->query->set("foo", "bar");

        $this->assertFalse(isset($request["baz"]));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    function testArrayAccessIsReadOnly()
    {
        $request = new Request;
        $request["foo"] = "bar";
    }

    /**
     * @expectedException \BadMethodCallException
     */
    function testUnsetThrowsException()
    {
        $request = new Request;
        unset($request["foo"]);
    }
}
