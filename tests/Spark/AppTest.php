<?php

namespace Spark\Test;

use Spark\Application,
    Spark\Http\Request,
    Spark\Http\Response,
    Underscore\Fn;

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
        $this->assertEquals("HEAD", $this->app->run($headRequest)->headers->get("X-Request-Method"));
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
}
