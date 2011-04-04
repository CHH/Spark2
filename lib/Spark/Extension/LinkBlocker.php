<?php

namespace Spark\Extension;

class LinkBlocker extends Base
{
    function blockLinksFrom($host)
    {
        $this->before(function($app) use ($host) {
            if (preg_match($host, $app->request->headers->get("referer"))) {
                $app->halt(403, "Go Away!");
            }
        });
    }
}
