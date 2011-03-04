<?php

namespace Spark\Extension;

class Redirecting extends Base
{
    function redirectTo($url, $code = 302)
    {
        return function() use ($url, $code) {
            $response = new \Spark\Http\RedirectResponse($url, $code);
            $response->send();
            die();
        };
    }
}

\Spark\register(__NAMESPACE__ . "\Redirecting");
