<?php

namespace Spark\Router;

interface Route
{
    public function match(\Spark\Controller\HttpRequest $request);
}
