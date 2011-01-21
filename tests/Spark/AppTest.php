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
    
    function testCanActAsSingleton()
    {
        $app1 = \Spark\App();
        $app2 = \Spark\App();
        
        $this->assertSame($app1, $app2);
    }
    
    function testSingletonInstanceIsExchangeable()
    {
        $app = new App;
        \Spark\App($app);
        
        $this->assertSame($app, \Spark\App());
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
