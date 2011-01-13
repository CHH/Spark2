<?php

namespace Spark\Test;

require_once TESTS . "/Spark/_data/TestController.php";

use Spark\HttpRequest, 
    Spark\HttpResponse, 
    Spark\Test\TestController;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    function testActioControllerCanReturnCallbackToAction()
    {
        $callback = TestController::action("index");
        
        $this->assertTrue(is_callable($callback));
        $this->assertEquals("indexAction", $callback(new HttpRequest, new HttpResponse));
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    function testActionControllerThrowsExceptionIfNoValidActionGiven()
    {
        $callback = TestController::action("foo");
    }
}
