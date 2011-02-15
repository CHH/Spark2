<?php

namespace Spark\Test\Util;

require_once TESTS . "/Spark/_data/SampleClass.php";

use Spark\Util,
    Spark\Test\SampleClass;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    function testConvertsKeysToCamelCaseAndCallsThemAsSetters()
    {
        $sample = new SampleClass;
        
        $options = array(
            "foo_bar" => "bar",
            "bar"     => "baz"
        );
        
        Util\set_options($sample, $options);
        
        $this->assertEquals($options["foo_bar"], $sample->fooBar);
        $this->assertEquals($options["bar"], $sample->bar);
    }
}
