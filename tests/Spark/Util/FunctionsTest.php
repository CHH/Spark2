<?php

namespace Spark\Test\Util;

use Spark\Util;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    function testCamelize()
    {
        $string1 = "foo-bar-baz";
        $string2 = "foo_bar_baz";
        
        $expect  = "FooBarBaz";
        
        $this->assertEquals($expect, Util\str_camelize($string1));
        $this->assertEquals($expect, Util\str_camelize($string2));
    }
    
    function testWordsToArray()
    {
        $string = "apple banana pear plum";
        $words  = Util\words($string);
        
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
        
        $newFn = Util\func_wrap($fn, $wrapper);
        
        $this->assertEquals($arg, $newFn($arg));
    }
    
    function testWrapFunctionGivenAsString()
    {
        $wrapper = function($original, $string) {
            return strtoupper($original($string));
        };
        $newFn = Util\func_wrap("\Spark\Util\str_camelize", $wrapper);
        
        $this->assertEquals("FOOBARBAZ", $newFn("foo_bar_baz"));
    }
    
    function testCurry()
    {
        $multiply = function($x, $y) {
            return $x * $y;
        };
        
        $double = Util\func_curry($multiply, 2);
        
        $this->assertEquals(2, $double(1));
    }
    
    function testCompose()
    {
        $greet = function($name) {
            return "Hello $name";
        };
        
        $exclaim = function($statement) {
            return $statement . "!";
        };
        
        $greetAndExclaim = Util\func_compose($greet, $exclaim);
        
        $this->assertEquals("Hello World!", $greetAndExclaim("World"));
    }
    
    function testBlockGiven()
    {
        $fn = function($block) {
            return Util\block_given(func_get_args());
        };
        
        $this->assertTrue($fn(function() {}));
    }
    
    function testBlockGivenAtOffset()
    {
        $fn = function($a, $block, $c) {
            return Util\block_given(func_get_args(), 1);
        };
        
        $this->assertTrue($fn("a", function() {}, "b"));
    }
    
    /**
     * @dataProvider arrayProvider
     */
    function testArrayDeleteKey($array)
    {
        $value = Util\array_delete_key("foo", $array);
        
        $this->assertEquals("bar", $value);
        $this->assertEmpty($array);
    }
    
    /**
     * @dataProvider arrayProvider
     */
    function testArrayDeleteValue($array)
    {
        $value = Util\array_delete("bar", $array);
        
        $this->assertEquals("bar", $value);
        $this->assertEmpty($array);
    }
    
    /**
     * @dataProvider arrayProvider
     */
    function testArrayDeleteAndKeyNotFound($array)
    {
        $value = Util\array_delete_key("notexistingkey", $array);
        
        $this->assertNull($value);
    }
    
    /**
     * @dataProvider arrayProvider
     */
    function testArrayDeleteAndValueNotFound($array)
    {
        $value = Util\array_delete("notexistingvalue", $array);
        
        $this->assertNull($value);
    }
    
    function arrayProvider()
    {
        return array(
            array(array("foo" => "bar")),
        );
    }
}
