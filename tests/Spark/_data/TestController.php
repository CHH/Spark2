<?php

namespace Spark\Test;

use SparkCore\Request,
    SparkCore\Response,
    Spark\Controller\ActionController;

class TestController extends ActionController
{
    function indexAction(Request $request, Response $response)
    {
        return __FUNCTION__;
    }
}
