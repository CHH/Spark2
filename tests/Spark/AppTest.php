<?php

namespace Spark\Test;

use Spark\App,
    Spark\HttpRequest,
    Spark\HttpResponse;

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
        
        $this->assertEquals("bar", $app->getOption("foo"));
    }
    
    function testContainsARouterInstance()
    {
        $app = $this->app;
        $this->assertInstanceOf("\Spark\Router", $app->route());
    }
}
