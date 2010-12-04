<?php

namespace Spark;

class SampleClass
{
    public $fooBar, $bar;
    
    function setFooBar($foo)
    {
        $this->fooBar = $foo;
    }
    
    function setBar($bar)
    {
        $this->bar = $bar;
    }
}

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $sample = new SampleClass;
        
        $options = array(
            "foo_bar" => "bar",
            "bar"     => "baz"
        );
        
        Options::setOptions($sample, $options);
        
        $this->assertEquals($options["foo_bar"], $sample->fooBar);
        $this->assertEquals($options["bar"], $sample->bar);
    }
}
