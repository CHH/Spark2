<?php
/**
 * A small sample extension for blocking references from certain sites
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Extension
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Extension;

class LinkBlocker extends Base
{
	/**
	 * Block links from hosts matching the pattern, halts the application
	 * if the pattern matches the request's referer
	 *
	 * @param string $host Regular expression
	 */
    function blockLinksFrom($host)
    {
        $this->before(function($app) use ($host) {
            if (preg_match($host, $app->request->headers->get("referer"))) {
                $app->halt(403, "Go Away!");
            }
        });
    }
}
