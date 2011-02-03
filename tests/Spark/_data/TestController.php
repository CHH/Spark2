<?php

namespace Spark\Test;

use SparkCore\HttpRequest,
    SparkCore\HttpResponse,
    Spark\Controller\ActionController;

class TestController extends ActionController
{
    function indexAction(HttpRequest $request, HttpResponse $response)
    {
        return __FUNCTION__;
    }
}
