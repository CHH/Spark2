<?php

namespace Spark;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $router   = new Router;
        $request  = new Controller\HttpRequest;
        $response = new Controller\HttpResponse;
        $self     = $this;
        
        $request->setRequestUri("/");
        
        $router->get("/", function($request, $response) {
            echo "Root matched";
        });
        
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
        
        $router->get("users/:id",    $getHandler, array("id" => 23));
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
}
