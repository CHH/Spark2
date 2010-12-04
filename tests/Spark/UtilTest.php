<?php

namespace Spark;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    function testCamelize()
    {
        $string1 = "foo-bar-baz";
        $string2 = "foo_bar_baz";
        
        $expect  = "FooBarBaz";
        
        $this->assertEquals($expect, string_camelize($string1));
        $this->assertEquals($expect, string_camelize($string2));
    }
    
    function testWordsToArray()
    {
        $string = "apple banana pear plum";
        $words  = words($string);
        
        $this->assertEquals(4, sizeof($words));
    }
    
    function testWrapFunction()
    {
        $arg = "foo";
        
        $wrapper = function($original, $arg) {
            return $original($arg);
        };
        
        $fn = function($arg) {
            return $arg;
        };
        
        $newFn = func_wrap($fn, $wrapper);
        
        $this->assertEquals($arg, $newFn($arg));
    }
    
    function testWrapFunctionGivenAsString()
    {
        $wrapper = function($original, $string) {
            return strtoupper($original($string));
        };
        $newFn = func_wrap("string_camelize", $wrapper);
        
        $this->assertEquals("FOOBARBAZ", $newFn("foo_bar_baz"));
    }
    
    /**
     * @dataProvider arrayProvider
     */
    function testArrayDeleteKey($array)
    {
        $value = array_delete_key("foo", $array);
        
        $this->assertEquals("bar", $value);
        $this->assertEmpty($array);
    }
    
    /**
     * @dataProvider arrayProvider
     */
    function testArrayDeleteValue($array)
    {
        $value = array_delete("bar", $array);
        
        $this->assertEquals("bar", $value);
        $this->assertEmpty($array);
    }
    
    /**
     * @dataProvider arrayProvider
     */
    function testArrayDeleteAndKeyNotFound($array)
    {
        $value = array_delete_key("notexistingkey", $array);
        
        $this->assertNull($value);
    }
    
    /**
     * @dataProvider arrayProvider
     */
    function testArrayDeleteAndValueNotFound($array)
    {
        $value = array_delete("notexistingvalue", $array);
        
        $this->assertNull($value);
    }
    
    function arrayProvider()
    {
        return array(
            array(array("foo" => "bar")),
        );
    }
}
