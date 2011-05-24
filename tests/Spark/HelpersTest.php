<?php

namespace Spark\Test;

use Spark\Http\Request,
    Spark\Http\Response,
    Spark as s;

class HelpersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Spark\HaltException
     */
    function testHaltThrowsHaltException()
    {
        s\halt("Hello World", 200, array("foo" => "bar"));
    }

    function testHaltAlsoTakesResponseObjectAsFirstArgument()
    {
        try {
            s\halt(new Response("Hello World"));
            
        } catch (\Spark\HaltException $e) {
            $this->assertEquals("Hello World", $e->getResponse()->getContent());
        }
    }

    function testHaltExceptionContainsResponseObject()
    {
        try {
            s\halt("Hello World", 200, array("foo" => "bar"));

        } catch (\Spark\HaltException $e) {
            $response = $e->getResponse();
            $this->assertTrue($response instanceof Response);

            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals("Hello World", $response->getContent());
            $this->assertEquals("bar", $response->headers->get("foo"));

        } catch (\Exception $e) {
            $this->fail("No HaltException: $e");
        }
    }

    /**
     * @expectedException \Spark\PassException
     */
    function testPassThrowsPassException()
    {
        s\pass();
    }

    function testWithUserAgent()
    {
        $request = new Request;
        $request->headers->set("User-Agent", "foo");

        $resp = s\withUserAgent($request, "/foo/", function() {
            return "Foo";
        });
        $this->assertEquals("Foo", $resp);

        $resp = s\withUserAgent($request, "/bar/", function() {});
        $this->assertFalse($resp);
    }

    function testWithHostName()
    {
        $request = new Request;
        $request->headers->set("Host", "www.example.com");

        $resp = s\withHostName($request, "/example.com/", function() {
            return "Foo";
        });
        $this->assertEquals("Foo", $resp);

        $resp = s\withHostName($request, "/foobar.com/", function() {});
        $this->assertFalse($resp, "Returns false if there is no match");
    }

    function testWithFormat()
    {
        $request = new Request;
        $request->headers->set("Accept", "application/json");
        $called = 0;

        s\withFormat($request, "json", function() use (&$called) {
            $called++;
        });
        $this->assertEquals(1, $called);
    }

    function testWithFormatTakesArrayDefinition()
    {
        $request = new Request;
        $request->headers->set("Accept", "application/json");

        $h = function() { 
            return "Foo";
        };

        $resp = s\withFormat($request, array(
            "html" => $h,
            "json" => $h
        ));

        $this->assertEquals("Foo", $resp);
    }

    function testWithFormatReturnsFalseIfNoMatch()
    {
        $request = new Request;

        $h = function() {
            return "Foo";
        };

        $resp = s\withFormat($request, array(
            "html" => $h,
            "json" => $h
        ));

        $this->assertFalse($resp);
    }
}
