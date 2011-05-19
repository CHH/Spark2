<?php

namespace Spark\Test;

class SettingsTest extends \PHPUnit_Framework_TestCase
{
    protected $settings;

    function setUp()
    {
        $this->settings = new \Spark\Settings;
    }
    
    function testCanEnableSettings()
    {
        $this->settings->enable("foo");
        $this->assertTrue($this->settings->get("foo"));
    }
    
    function testCanDisableSettings()
    {   
        $this->settings->disable("foo");
        $this->assertFalse($this->settings->get("foo"));
    }
    
    function testSimpleSet()
    {
        $this->settings->set("foo", "bar");
        
        $this->assertEquals("bar", $this->settings->get("foo"));
    }
    
    function testGetWithNoArgumentsReturnsAllOptions()
    {
        $values = array("foo" => "foo", "bar" => "bar", "baz" => "baz");
        $this->settings->set($values);
        
        $this->assertEquals($values, $this->settings->get());
    }
    
    function testSetTakesArraySpec()
    {
        $values = array("foo" => "bar", "bar" => "baz");
        $this->settings->set($values);
        
        $this->assertEquals($values["foo"], $this->settings->get("foo"));
        $this->assertEquals($values["bar"], $this->settings->get("bar"));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testSetWithANonScalarValueAsOptionNameThrowsAnException()
    {
        $key = (object) array(
            "foo" => "bar"
        );
        $this->settings->set($key, "Hello World");
    }
}
