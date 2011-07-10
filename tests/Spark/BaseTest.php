<?php

namespace Spark\Test;

class TestExtension extends \Spark\Base
{
    function sayHelloWorld()
    {
        return "Hello World";
    }

    function registered($app)
    {
        $app->set('foo', 'bar');
    }
}

class BaseTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    function setUp()
    {
        $this->app = new \Spark\Application;
    }

    function testExtensions()
    {
        $this->app->register(new TestExtension);

        $this->assertEquals('Hello World', $this->app->sayHelloWorld());
    }

    function testRegisterTriggersRegisteredHook()
    {
        $this->app->register(new TestExtension);

        $this->assertEquals('bar', $this->app->settings->get('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testExtensionMustBeAnObjectOrClassName()
    {
        $this->app->register('\\Spark\\Foo\\Bar');
    }

    function testRegisterTakesAClassName()
    {
        $this->app->register("Spark\\Test\\TestExtension");

        $this->assertEquals('Hello World', $this->app->sayHelloWorld());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    function testThrowsExceptionIfMethodWasNotFound()
    {
        $this->app->someNonExistingExtensionMethod();
    }
}
