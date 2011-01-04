<?php

namespace Spark\Test;

use Spark\HttpRequest, Spark\HttpResponse, Spark\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->router   = new Router;
        $this->request  = new HttpRequest;
        $this->response = new HttpResponse;
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
