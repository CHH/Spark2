<?php

namespace Spark\Test;

use Spark\Application,
    Spark\Http\Request,
    Spark\Http\Response,
    Underscore\Fn;

class TestController
{
    function __invoke($request)
    {
        return new Response("Hello World");
    }
}

class AppTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->app = new Application;
        $this->app->disable("send_response");
    }
    
    function testGetDefinesHeadHandler()
    {
        $this->app->get("/", function($request) {
            $method = $request->getMethod();
            return new Response("", 200, array("x-request-method" => $method));
        });
        
        $request = Request::create("/", "GET");
        $this->assertEquals("GET", $this->app->run($request)->headers->get("X-Request-Method"));
        
        $headRequest = Request::create("/", "HEAD");
        $this->assertEquals(
            "HEAD", $this->app->run($headRequest)->headers->get("X-Request-Method")
        );
    }
    
    function testRoutesCanTakeParams()
    {
        $value;
    
        $this->app->get("/:foo", function($request) use (&$value) {
            $value = $request["foo"];
        });
        
        $this->app->run(Request::create("/bar"));
        
        $this->assertEquals("bar", $value);
    }
    
    function testNoRouteMatchedTriggersNotFound()
    {
        $response = new Response("404 You dumb Mug!", 404);
        
        $this->app->post("/foo.html", function() {});
        
        $this->app->notFound(function() use ($response) {
            return $response;
        });
        
        $this->assertEquals($response, $this->app->run(Request::create("/index.html")));
    }

    function testRunsBeforeFilters()
    {
        $request = Request::create("/bar");

        $this->app->before(function($req) {
            return new Response("Hello World");
        });

        $this->app->get("/bar", function($req) {
            return false;
        });

        $response = $this->app->run($request);

        $this->assertEquals("Hello World", $response->getContent());
    }

    function testCanHaveConfiguratorsForAnyEnvironment()
    {
        $called = 0;
        $inc = function() use (&$called) { $called++; };
        $this->app->configure($inc);
        $this->app->configure("production", $inc);
        $this->app->configure("development", $inc);
        
        $this->app->set("environment", "production");

        $this->app->run();

        $this->assertEquals(2, $called);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testConfigureThrowsExceptionIfInvalidCallbackGiven()
    {
        $this->app->configure("foo");
    }

    function testCanEnableAndDisableSettings()
    {
        $this->app->enable("foo");
        $this->assertTrue($this->app->settings->get("foo"));

        $this->app->disable("foo");
        $this->assertFalse($this->app->settings->get("foo"));
    }

    function testInstantiatesCallbackClasses()
    {
        $request = Request::create("/foo");
        $this->app->get("/foo", "\\Spark\\Test\\TestController");

        $response = $this->app->run($request);
        $this->assertEquals("Hello World", $response->getContent());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testThrowsExceptionIfRouteCallbackIsInvalidClass()
    {
        $this->app->get("/foo", "\\Foo\\Bar\\Baz");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testThrowsExceptionIfRouteCallbackIsNotACallback()
    {
        $this->app->get("/foo", "foobarbazbu");
    }
}
