<?php

namespace Spark\Test\Util;

use Spark\Util\StringExpression;

class StringExpressionTest extends \PHPUnit_Framework_TestCase
{
    function testCanReturnRegexWithoutDelimiters()
    {
        $expr = new StringExpression("/foo/:bar");

        $this->assertEquals("\/foo\/(?<bar>[a-zA-Z0-9\-\_]+)", $expr->toRegExp(false));
    }

    function testCanInsertCustomSubPatternsForVariables()
    {
        $expr = new StringExpression("/foo/:bar", array("bar" => "\d+"));
        $this->assertEquals("#^\/foo\/(?<bar>\d+)$#", $expr->toRegExp());
    }

    function testToStringReturnsCompiledRegex()
    {
        $expr = new StringExpression("/foo/:bar");

        $this->assertEquals("#^\/foo\/(?<bar>[a-zA-Z0-9\-\_]+)$#", (string) $expr);
    }
}
