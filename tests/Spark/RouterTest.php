<?php

namespace Spark\Test;

use Spark\Http\Request, 
    Spark\Router,
    Spark\Router\RestRoute,
    Spark\Util,
    PHPUnit_Framework_Assert as Assert;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->router  = new Router;
        $this->request = $this->getMock("Spark\Http\Request");
    }
    
    function testRoutesCanBeAssembledToUrl()
    {
        return $this->markTestSkipped(
            "Route naming and assembling is currently not supported"
        );
        
        $route  = new RestRoute("/:a/:b/:c");
        $params = array("a" => "say", "b" => "hello", "c" => "world");
        
        $this->assertEquals("/say/hello/world", $route->assemble($params));
    }
    
    function testRoutesCanContainOptionalParams()
    {
        $router  = $this->router;
        $request = $this->request;

        $request->expects($this->any())
                ->method("getRequestUri")
                ->will($this->returnValue("/users/23"));

        $router->match("/users(/:id)?", "index#index")->defaults(array("id" => "foo"));

        $router->route($request);
        $this->assertEquals("foo", $request->attributes->get("id"));

        // Check overriding
        $request->setRequestUri("/users/23");
        
        $router->route($request);
        $this->assertEquals(23, (int) $request->attributes->get("id"));
    }

    function testProvidesOptionsViaAFluentInterface()
    {
        $router   = $this->router;
        $request  = $this->request;
        
        $request->setRequestUri("/users/23");

        $router->match("/users/:id", "index#index")->meta("foo", "bar");
        
        $router($request);

        $this->assertEquals(23, $request->meta("id"));
        $this->assertEquals("bar", $request->meta("foo"));
    }
    
    function testMatchesHttpMethods()
    {
        $router   = $this->router;
        $request  = $this->request;
        $self     = $this;
        
        $request->setRequestUri("/users/23");
        
        $handlersCalled = 0;
        
        $testHandler = function() use (&$handlersCalled) {
            $handlersCalled++;
        };
        
        $router->get("users/23",    $testHandler);
        $router->post("users/23",   $testHandler);
        $router->put("users/23",    $testHandler);
        $router->delete("users/23", $testHandler);
        
        foreach (Util\words("GET POST PUT DELETE") as $method) {
            $request->setMethod($method);
            $callback = $router->route($request);
            $callback($request);
        }
        
        $this->assertEquals(4, $handlersCalled, "Not all handlers were called");
    }
    
    function testRoutesCanBeNamed()
    {
        return $this->markTestSkipped("Route naming is currently not supported");
        
        $router = $this->router;
        
        $router->match("/users/:name", "users#view")->name("users_route");
        
        $this->assertInstanceOf(
            "\Spark\Router\RestRoute", $router->getRoute("users_route")
        );
    }
    
    function testRoutesCanBeScoped()
    {
        $router   = $this->router;
        $request  = $this->request->setRequestUri("/admin/users/23")->setMethod("GET");
        
        $testcase = $this;
        
        $router->scope("admin", function($admin) use ($testcase) {
            $admin->get("users/:id", function($request) use ($testcase) {
                $testcase->assertEquals(23, (int) $request->meta("id"));
                
                // Test if scope name is set as metadata if scoped route gets matched
                $testcase->assertEquals("admin", $request->meta("scope"));
            });
            
            $admin->get("posts/:id", function() use ($testcase) {
                $testcase->fail();
            });
        });
        
        $callback = $router->route($request);
        call_user_func($callback, $request);
    }
    
    function testRegistersScopeNameAsMetadataWithRoute()
    {
        $router   = $this->router;
        $request  = $this->request->setRequestUri("/admin/users")->setMethod("GET");
        
        $router->scope("admin", function($admin) {
            $admin->get("users", "users#index");
        });

        $router->route($request);
        
        $this->assertEquals("admin", $request->meta("scope"));
    }
    
    /**
     * @expectedException Spark\Router\Exception
     */
    function testThrowsRouterExceptionIfNoRouteMatched()
    {
        $router = new Router;
        
        $request = $this->request;
        $request->setRequestUri("/foo/bar")->setMethod("GET");
        
        $router->get("foo", function() {});
        
        $router->route($request);
    }
}
