<?php

namespace Spark\Test\Util;

use PHPUnit_Framework_TestCase,
    Spark\Util\StringObject;

class StringObjectTest extends PHPUnit_Framework_TestCase
{
    function testIsCountable()
    {
        $string = new StringObject("foo");
        $this->assertEquals(3, count($string));
    }
    
    function testCanBeLowercased()
    {
        $string = new StringObject("FOO");
        $this->assertEquals("foo", (string) $string->toLower());
    }
    
    function testCanBeCapitalized()
    {
        $string = new StringObject("foo");
        $this->assertEquals("FOO", (string) $string->toUpper());
    }
    
    function testDashedStringCanBeCamelized()
    {
        $string = new StringObject("foo-bar-baz");
        $this->assertEquals("FooBarBaz", (string) $string->camelize());
    }
    
    function testUnderscoredStringCanBeCamelized()
    {
        $string = new StringObject("foo_bar_baz");
        $this->assertEquals("FooBarBaz", (string) $string->camelize());
    }
    
    function testIsExchangeable()
    {
        $string = new StringObject("foo");
        $string->exchangeString("bar");
        $this->assertEquals("bar", (string) $string);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    function testThrowsExceptionIfNotExchangedWithString()
    {
        $string = new StringObject("foo");
        $string->exchangeString(42);
    }
    
    function testReturnsStringCopy()
    {
        $string = new StringObject("foo");
        $this->assertEquals("foo", $string->getStringCopy());
    }
}
