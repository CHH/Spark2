<?php

namespace Spark\Test\Util;

use PHPUnit_Framework_TestCase,
    Spark\Util\ArrayObject;

class ArrayObjectTest extends PHPUnit_Framework_TestCase
{
    function testDeletesKeyAndReturnsValue()
    {
        $array = new ArrayObject(array("foo" => "bar", "bar" => "baz"));
        $this->assertEquals("bar", $array->deleteKey("foo"));
        $this->assertArrayNotHasKey("foo", (array) $array);
    }
    
    function testReturnsNullIfKeyWasNotFound()
    {
        $array = new ArrayObject(array("foo" => "bar", "bar" => "baz"));
        $this->assertNull($array->deleteKey("baz"));
    }

    function testDeletesKeyWhereValueGotFound()
    {
        $array = new ArrayObject(array("foo" => "bar", "bar" => "baz"));
        $this->assertEquals("baz", $array->delete("baz"));
        $this->assertArrayNotHasKey("bar", (array) $array);
    }
    
    function testReturnsNullIfValueWasNotFound()
    {
        $array = new ArrayObject(array("foo" => "bar", "bar" => "baz"));
        $this->assertNull($array->delete("foo"));
    }
    
    function testSliceAltersTheArray()
    {
        $array = new ArrayObject(array("foo", "bar", "baz"));
        $array->slice(2);
        $this->assertEquals(2, count($array));
    }
    
    function testSliceWithOffset()
    {
        $array = new ArrayObject(array("foo", "bar", "baz"));
        $array->slice(2, 1);
        $this->assertEquals("bar", $array[0]);
    }
}
