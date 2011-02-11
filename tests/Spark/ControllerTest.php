<?php

namespace Spark\Test;

require_once TESTS . "/Spark/_data/TestController.php";

use Spark\Http\Request, 
    Spark\Http\Response, 
    Spark\Test\TestController;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    function testActionControllerCanReturnCallbackToAction()
    {
        $callback = TestController::action("index");
        
        $this->assertTrue(is_callable($callback));
        $this->assertEquals("indexAction", $callback(new Request));
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    function testActionControllerThrowsExceptionIfNoValidActionGiven()
    {
        $callback = TestController::action("foo");
    }
}
