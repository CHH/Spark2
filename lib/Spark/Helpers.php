<?php
/**
 * Helper Functions for the Request Scope
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Exception
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

use Spark\Http\Request,
    Spark\Http\Response,
    Underscore as _;

/**
 * Immediately halt the request execution and return the supplied response
 *
 * @param int $status
 * @param string $body
 * @param array $headers
 * @return Response;
 */
function halt($status = 200, $body = '', $headers = array())
{
    $response = new Response($body, $status, $headers);
    throw new HaltException($response);
}

/**
 * Skip to the next callback for the route
 */
function pass()
{
    throw new PassException;
}

/**
 * Matches the Request's User Agent with the supplied pattern and 
 * executes the callback if there's a Match
 *
 * @param  Request $request
 * @param  string  $pattern
 * @param  callback $callback
 * @return mixed
 */
function withUserAgent(Request $request, $pattern, $callback)
{
    $userAgent = $request->headers->get("User-Agent");

    if (preg_match($pattern, $userAgent)) {
        return call_user_func($callback, $request);
    }
    return false;
}

/**
 * Matches the Request's HTTP Host with the supplied pattern and 
 * executes the callback if there's a Match
 *
 * @param  Request  $request
 * @param  string   $pattern
 * @param  callback $callback
 * @return mixed
 */
function withHostName(Request $request, $pattern, $callback)
{
    $host = $request->getHost();

    if (preg_match($pattern, $host)) {
        return call_user_func($callback, $request);
    }
    return false;
}

/**
 * Matches the list of supplied formats with the mime types of the Request's
 * Accept Header and executes the callback if there're matches
 *
 * @param  Request      $request
 * @param  array|string $formats One or more Formats, e.g. "html", "xml" or "json"
 * @param  callback     $callback
 * @return mixed
 */
function withFormat(Request $request, $formats, $callback)
{
    $formats = (array) $formats;

    $hasFormat = _\chain($request->getAcceptableContentTypes())
        ->map(array($request, "getFormat"))
        ->select(function($value) use ($formats) {
            return in_array($value, $formats);
        })
        ->value();

    if ($hasFormat) {
        return call_user_func($callback, $request);
    }
    return false;
}





