<?php

namespace Spark\Router;

interface Route
{
    public function match(\Spark\HttpRequest $request);
}
