<?php

namespace Spark\Test;

use Spark\App;

class AppTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->app = new App;
    }    
    
    function testAppInstanceIsExchangeable()
    {
        Spark($this->app);
        
        $this->assertSame($this->app, Spark());
    }
    
    function testCanActAsSingleton()
    {
        $app1 = Spark();
        $app2 = Spark();
        
        $this->assertSame($app1, $app2);
    }
    
    function testHoldsMetadata()
    {
        $app = $this->app;
        $app->set("foo", "bar");
        
        $this->assertEquals("bar", $app->get("foo"));
    }
    
    function testSetTakesArrayOfOptions()
    {
        $app = $this->app;
        
        $values = array(
            "foo" => "bar",
            "bar" => "baz"
        );
        
        $app->set($values);
        
        $this->assertEquals("bar", $app->get("foo"));
        $this->assertEquals("baz", $app->get("bar"));
        
        return $values;
    }
    
    /**
     * @depends testSetTakesArrayOfOptions
     */
    function testGetWithNoArgumentsReturnsAllOptions($values)
    {
        $app = $this->app;
        $app->set($values);
        
        $this->assertEquals($values, $app->get());
    }
    
    function testContainsRouterInstance()
    {
        $app = $this->app;
        $this->assertInstanceOf("\Spark\Router", $app->getRouter());
    }
}
