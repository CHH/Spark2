<?php

namespace Spark;

class EventTest extends \PHPUnit_Framework_Testcase
{
    public function testObjectObserve()
    {
        $object = new \StdClass();
        
        $object->test = "test";
        $assertedMemo = "test";
        
        $self = $this; 
        
        $handler = function($subject, $memo) use ($self, $assertedMemo, $object) {
            $self->assertEquals($assertedMemo, $memo);
            $self->assertEquals($object, $subject);
        };
        
        Event::observe($object, "test:trigger", $handler);
        Event::trigger($object, "test:trigger", $assertedMemo);
    }
}
