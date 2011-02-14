<?php
/**
 * Simple Router
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_HttpResponse
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2011 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Http;

use InvalidArgumentException;

class Response
{
    protected $headers = array();
    protected $content = "";
    protected $status  = 200;

    function __construct($content = "", $status = null, Array $headers = array())
    {
        if (null !== $status) {
            $this->setStatus($status);
        }
        
        if ($headers) {
            $this->addHeaders($headers);
        }
    }
    
    function send()
    {
        $this->sendHeaders();        
        $this->sendContent();
        return $this;
    }
    
    function appendContent($content)
    {
        $this->content .= $content;
        return $this;
    }
    
    function prependContent($content)
    {
        $this->content = $content . $this->content;
        return $this;
    }
    
    function setContent($content = "")
    {
        if (!is_string((string) $content)) {
            throw new InvalidArgumentException("Content must be a string");
        }
        $this->content = $content;
        return $this;
    }
    
    function getContent()
    {
        return $this->content;
    }
    
    function sendContent()
    {
        print $this->content;
        return $this;
    }
    
    function setStatus($status)
    {
        if ($status < 100 or $status > 505) {
            throw new InvalidArgumentException("Invalid HTTP Status Code $status");
        }
        $this->status = $status;
        return $this;
    }
    
    function getStatus()
    {
        return $this->status;
    }
    
    function getHeaders()
    {
        return $this->headers;
    }
    
    function sendHeaders()
    {
        if (headers_sent($file, $line)) {
            return $this;
        }
        
        header("HTTP/1.1 " . $this->status);
        
        foreach ($this->headers as $header) {
            $header->send();
        }
        return $this;
    }
    
    function addHeader($header, $content = null, $replace = false)
    {
        if (!$header instanceof Header) {
            $header = new Header($header, $content, $replace);
        }
        if (true === $header->replace()) {
            foreach ($this->headers as $key => $h) {
                if ($h->getType() === $header->getType()) {
                    unset($this->headers[$key]);
                }
            }
        }
        $this->headers[] = $header;
        return $this;
    }
    
    function addHeaders(array $headers)
    {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }
        return $this;
    }    
}
