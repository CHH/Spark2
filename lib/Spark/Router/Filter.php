<?php

namespace Spark\Router;

use \Spark\HttpRequest;

interface Filter
{
    function __invoke(HttpRequest $request);
}
