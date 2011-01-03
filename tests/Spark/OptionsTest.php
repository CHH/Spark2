<?php

namespace Spark\Test;

require_once "_data/SampleClass.php";

use Spark\Util\Options;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $sample = new SampleClass;
        
        $options = array(
            "foo_bar" => "bar",
            "bar"     => "baz"
        );
        
        Options::setOptions($sample, $options);
        
        $this->assertEquals($options["foo_bar"], $sample->fooBar);
        $this->assertEquals($options["bar"], $sample->bar);
    }
}
