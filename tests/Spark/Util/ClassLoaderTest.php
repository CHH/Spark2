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
    
    function testActsAsSingleton()
    {
        $instance1 = Util\ClassLoader();
        $instance2 = Util\ClassLoader();
        $this->assertSame($instance1, $instance2);
    }
    
    function testCanBeRegisteredOnSplAutoloadStack()
    {
        $classLoader = $this->classLoader;
        $this->assertTrue(in_array($classLoader, spl_autoload_functions(), true));
    }
    
    function testCanBeUnregisteredFromSplAutoloadStack()
    {
        $classLoader = $this->classLoader;
        $classLoader->unregister();
        $this->assertFalse(in_array($classLoader, spl_autoload_functions(), true));
        
        $classLoader->register();
    }
    
    function testAutoloadFunctionRegistersClassesWithClassLoader()
    {
        autoload("Spark\Test\SampleClass", TESTS . "/Spark/_data/SampleClass.php");
        $this->assertTrue(class_exists("Spark\Test\SampleClass"), "Sample Class is not loadable");
    }
}
