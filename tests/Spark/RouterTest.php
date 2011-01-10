<?php

namespace Spark\Test;

use Spark\HttpRequest, 
    Spark\HttpResponse, 
    Spark\Router,
    Spark\Router\RestRoute,
    PHPUnit_Framework_Assert;

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
    
    /**
     * TODO: Implement required logic in router
     */
    function testRoutesCanContainOptionalParams()
    {
        $router  = $this->router;
        $request = $this->request;
        
        $request->setRequestUri("/users/");
        
        $callback = function($request) {
            var_dump($request);
        };
        
        $router->match(array("/users/(:id)" => $callback));
        $result = $router($request);
        $result($request);
    }
    
    function testTakesOptionsArrayAsArgument()
    {
        $router   = $this->router;
        $request  = $this->request;
        $testcase = $this;
        
        $request->setRequestUri("/users/23");
        
        $callback = function($request, $response) use ($testcase) {
            $testcase->assertEquals(23, $request->getMetadata("id"));
            $testcase->assertEquals("bar", $request->getMetadata("foo"));
        };
        
        $router->match(array("/users/:id" => $callback, "as" => "user_route", "foo" => "bar"));
        
        $result = $router($this->request);
        $result($this->request, $this->response);
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
            $self->assertEquals(23, (int) $request->getMetadata("id"));
        };
        
        $postHandler = function($request, $response) use ($self) {
            $self->assertEquals("POST", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->getMetadata("id"));
        };
        
        $putHandler = function($request, $response) use ($self) {
            $self->assertEquals("PUT", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->getMetadata("id"));
        };
        
        $deleteHandler = function($request, $response) use ($self) {
            $self->assertEquals("DELETE", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->getMetadata("id"));
        };
        
        $router->get("users/:id",    $getHandler);
        $router->post("users/:id",   $postHandler);
        $router->put("users/:id",    $putHandler);
        $router->delete("users/:id", $deleteHandler);
        
        foreach (words("GET POST PUT DELETE") as $method) {
            $request->setMethod($method);
            $callback = $router->route($request);
            $callback($request, $response);
        }
    }
    
    function testRoutesCanBeScoped()
    {
        $router   = $this->router;
        $request  = $this->request->setRequestUri("/admin/users/23")->setMethod("GET");
        $response = $this->response;
        
        $self = $this;
        
        $router->scope("admin", function($admin) use ($self) {
            $admin->get("users/:id", function($request, $response) use ($self) {
                $self->assertEquals(23, (int) $request->getMetadata("id"));
                
                // Test set of scope name as metadata
                $self->assertEquals("admin", $request->getMetadata("scope"));
            });
            
            $admin->get("posts/:id", function() use ($self) {
                $self->fail();
            });
        });
        
        $callback = $router->route($request);
        $callback($request, $response);
    }
    
    /**
     * @expectedException Spark\Router\Exception
     */
    function testThrowsRouterExceptionIfNoRouteMatched()
    {
        $router = new Router;
        
        $request = $this->request;
        $request->setRequestUri("foo/bar")->setMethod("GET");
        
        $router->get("foo", function() {});
        
        $router->route($request);
    }
}
