<?php

namespace Spark\Test;

use Spark\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
    function testScopesEventsToObjects()
    {
        $object = new \StdClass();
        
        $object->test = "test";
        $assertedMemo = "test";
        
        $self = $this; 
        
        $handler = function($memo) use ($self, $assertedMemo) {
            $self->assertEquals($assertedMemo, $memo);
        };
        
        Event::observe($object, "test:trigger", $handler);
        Event::fire($object, "test:trigger", $assertedMemo);
    }
}
