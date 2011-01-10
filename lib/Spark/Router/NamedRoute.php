<?php

namespace Spark\Router;

interface NamedRoute extends Route
{
    function setName($name);
    function getName();
    function assemble(Array $params);
}
