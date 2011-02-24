<?php
/**
 * The Response for the client
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Http
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Http;

use Symfony\Component\HttpFoundation;

class Response extends HttpFoundation\Response
{
    function merge($response)
    {
        if (is_array($response) or $response instanceof \Traversable) {
            foreach ($response as $r) {
                $this->merge($r);
            }
            return $this;
        }
        
        if (!$response instanceof Response) {
            return $this;
        }
        $this->setStatusCode($response->getStatusCode());
        $this->write($response->getContent());
        $this->headers->add($response->headers->all());
    }
    
    function write($content)
    {
        $this->content .= $content;
        return $this;
    }
}
