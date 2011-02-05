<?php

namespace Spark\Test;

require_once TESTS . "/Spark/_data/TestController.php";

use SparkCore\Request, 
    SparkCore\Response, 
    Spark\Test\TestController;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    function testActionControllerCanReturnCallbackToAction()
    {
        $callback = TestController::action("index");
        
        $this->assertTrue(is_callable($callback));
        $this->assertEquals("indexAction", $callback(new Request, new Response));
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    function testActionControllerThrowsExceptionIfNoValidActionGiven()
    {
        $callback = TestController::action("foo");
    }
}
