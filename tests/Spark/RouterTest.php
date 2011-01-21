<?php

namespace Spark\Test;

use Spark\HttpRequest, 
    Spark\HttpResponse, 
    Spark\Router,
    Spark\Router\RestRoute,
    Spark\Util,
    PHPUnit_Framework_Assert as Assert;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->router   = new Router;
        $this->request  = new HttpRequest;
        $this->response = new HttpResponse;
    }    
    
    function testRoutesCanBeAssembledToUrl()
    {
        $route  = new RestRoute(array("/:a/:b/:c" => "index#index"));
        $params = array("a" => "say", "b" => "hello", "c" => "world");
        
        $this->assertEquals("/say/hello/world", $route->assemble($params));
    }
    
    function testRoutesCanContainOptionalParams()
    {
        $router  = $this->router;
        $request = $this->request;
        
        $request->setRequestUri("/users");
        
        $router->match(array("/users(/:id)?" => "index#index", "id" => "foo"));

        $router->route($request);
        $this->assertEquals("foo", $request->meta("id"));

        // Check overriding
        $request->setRequestUri("/users/23");
        
        $router->route($request);
        $this->assertEquals(23, (int) $request->meta("id"));
    }
    
    function testTakesOptionsArrayAsArgument()
    {
        $router   = $this->router;
        $request  = $this->request;
        
        $request->setRequestUri("/users/23");
        
        $router->match(array("/users/:id" => "index#index", "as" => "users_route", "foo" => "bar"));
        
        $router($request);

        $this->assertEquals(23, $request->meta("id"));
        $this->assertEquals("bar", $request->meta("foo"));
    }
    
    function testProvidesMethodsToHandleHttpVerbs()
    {
        $router   = $this->router;
        $request  = $this->request;
        $response = $this->response;
        $self     = $this;
        
        $request->setRequestUri("/users/23");
        
        $getHandler = function($request, $response) use ($self) {
            $self->assertEquals("GET", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->meta("id"));
        };
        
        $postHandler = function($request, $response) use ($self) {
            $self->assertEquals("POST", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->meta("id"));
        };
        
        $putHandler = function($request, $response) use ($self) {
            $self->assertEquals("PUT", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->meta("id"));
        };
        
        $deleteHandler = function($request, $response) use ($self) {
            $self->assertEquals("DELETE", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->meta("id"));
        };
        
        $router->get("users/:id",    $getHandler);
        $router->post("users/:id",   $postHandler);
        $router->put("users/:id",    $putHandler);
        $router->delete("users/:id", $deleteHandler);
        
        foreach (Util\words("GET POST PUT DELETE") as $method) {
            $request->setMethod($method);
            $callback = $router->route($request);
            $callback($request, $response);
        }
    }
    
    function testRoutesCanBeNamed()
    {
        $router = $this->router;
        
        $router->match(array("/users/:name" => "users#view", "as" => "users_route"));
        $this->assertInstanceOf("\Spark\Router\RestRoute", $router->getRoute("users_route"));
    }
    
    function testRoutesCanBeScoped()
    {
        $router   = $this->router;
        $request  = $this->request->setRequestUri("/admin/users/23")->setMethod("GET");
        $response = $this->response;
        
        $testcase = $this;
        
        $router->scope("admin", function($admin) use ($testcase) {
            $admin->get("users/:id", function($request, $response) use ($testcase) {
                $testcase->assertEquals(23, (int) $request->meta("id"));
                
                // Test if scope name is set as metadata if scoped route gets matched
                $testcase->assertEquals("admin", $request->meta("scope"));
            });
            
            $admin->get("posts/:id", function() use ($testcase) {
                $testcase->fail();
            });
        });
        
        $callback = $router->route($request);
        $callback($request, $response);
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
     * @expectedException \BadMethodCallException
     */
    function testBindingToInvalidHttpMethodThrowsException()
    {
        // There is no HTTP Method named "destroy"
        $this->router->destroy(array("/foo/bar" => "foo#bar"));
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
