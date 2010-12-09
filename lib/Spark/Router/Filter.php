<?php

namespace Spark\Router;

use \Spark\Controller;

interface Filter
{
    function __invoke(Controller\HttpRequest $request);
}
