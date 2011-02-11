<?php

namespace Spark\Test;

use Spark\Http\Request,
    Spark\Controller\ActionController;

class TestController extends ActionController
{
    function indexAction(Request $request)
    {
        return __FUNCTION__;
    }
}
