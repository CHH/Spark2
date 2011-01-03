<?php

namespace Spark\Test\Util;

use PHPUnit_Framework_TestCase,
    Spark\Util;

class ClassLoaderTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->classLoader = Util\ClassLoader();
    }
    
    function testClassLoaderActsAsSingleton()
    {
        $instance1 = Util\ClassLoader();
        $instance2 = Util\ClassLoader();
        $this->assertEquals($instance1, $instance2);
    }
    
    function testClassLoaderRegistersOnSplAutoloadStack()
    {
        $classLoader = $this->classLoader;
        $this->assertTrue(in_array($classLoader, spl_autoload_functions(), true));
    }
    
    function testClassLoaderCanBeUnregisteredFromSplAutoloadStack()
    {
        $classLoader = $this->classLoader;
        $classLoader->unregister();
        $this->assertFalse(in_array($classLoader, spl_autoload_functions(), true));
    }
    
    function testAutoloadFunction()
    {
        autoload("Spark\Test\SampleClass", TESTS . "/Spark/_data/SampleClass.php");
        $this->assertTrue(class_exists("Spark\Test\SampleClass"), "Sample Class is not loadable");
    }
}
