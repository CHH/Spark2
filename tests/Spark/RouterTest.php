<?php

namespace Spark;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->router   = new Router;
        $this->request  = new Controller\HttpRequest;
        $this->response = new Controller\HttpResponse;
    }    
    
    public function test()
    {
        $router   = $this->router;
        $request  = $this->request;
        $response = $this->response;
        $self     = $this;
        
        $request->setRequestUri("/users/23");
        
        $getHandler = function($request, $response) use ($self) {
            $self->assertEquals("GET", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->getParam("id"));
        };
        
        $postHandler = function($request, $response) use ($self) {
            $self->assertEquals("POST", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->getParam("id"));
        };
        
        $putHandler = function($request, $response) use ($self) {
            $self->assertEquals("PUT", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->getParam("id"));
        };
        
        $deleteHandler = function($request, $response) use ($self) {
            $self->assertEquals("DELETE", strtoupper($request->getMethod()));
            $self->assertEquals(23, (int) $request->getParam("id"));
        };
        
        $router->get("users/:id",    $getHandler);
        $router->post("users/:id",   $postHandler);
        $router->put("users/:id",    $putHandler);
        $router->delete("users/:id", $deleteHandler);
        
        foreach (words("GET POST PUT DELETE") as $method) {
            $request->setMethod($method);
            $router->route($request);
            $callback = $request->getUserParam("callback");
            $callback($request, $response);
        }
    }
    
    /**
     * @expectedException Spark\Controller\Exception
     */
    public function testNoMatchException()
    {
        $router = new Router;
        
        $request = $this->request;
        $request->setRequestUri("foo/bar")->setMethod("GET");
        
        $router->get("foo", function() {});
        
        $router->route($request);
    }
}
