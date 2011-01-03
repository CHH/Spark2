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

namespace Spark;

class HttpResponse
{
    protected $headers = array();
    protected $body = "";    
    protected $code = 200;
    protected $exceptions = array();
    protected $renderExceptions = true;
    
    function setCode($code)
    {
        $this->code = $code;
    }

    function addException(\Exception $e) 
    {
        $this->exceptions[] = $e;
        return $this;
    }

    function renderExceptions($enabled = true)
    {
        $this->renderExceptions = $enabled;
        return $this;
    }

    function hasExceptions()
    {
        return (bool) $this->exceptions;
    }

    function getExceptions()
    {
        return $this->exceptions;
    }
    
    function header($header, $value, $replace = false)
    {
        $name  = $this->normalizeHeader($header);
        $value = (string) $value;
        
        if ($replace) {
            foreach ($this->headers as $header => $options) {
                if ($header == $name) {
                    unset($this->headers[$header]);
                }
            }
        }
        
        $this->headers[] = array(
            "name"  => $name,
            "value" => $value,
            "replace" => $replace
        );
        
        return $this;
    }
    
    function clearHeaders()
    {
        $this->headers = array();
        return $this;
    }
    
    function sendHeaders()
    {
        $ok = headers_sent($file, $line);
        
        if (!$ok) {
            return $this;
        }
        
        $httpCodeSent = false;
        
        foreach ($headers as $header) {
            if (!$httpCodeSent and $this->code) {
                header($header["name"] . ": " . $header["value"], $header["replace"], $this->code);
                $httpCodeSent = true;
            } else {
                header($header["name"] . ": " . $header["value"], $header["replace"]);
            }
        }
        
        if (!$httpCodeSent) {
            header("HTTP/1.1" . $this->code);
            $httpCodeSent = true;
        }
        return $this;
    }
    
    function prepend($body)
    {
        $this->body = $body . $this->body;
        return $this;
    }
    
    function append($body)
    {
        $this->body .= $body;
        return $this;
    }
    
    function clearBody()
    {
        $this->body = "";
    }
    
    function getBody()
    {
        return $this->body;
    }
    
    function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    
    function sendBody()
    {
        print $this->body;
    }
    
    function send()
    {
        $this->sendHeaders();
        
        if ($this->renderExceptions and $this->exceptions) {
            $this->prepend(join($this->exceptions, "<br>"));
        }
        
        $this->sendBody();
    }
    
    function toString()
    {
        ob_start();
        $this->send();
        return ob_get_clean();
    }
    
    function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeHeader($name)
    {
        $filtered = str_replace(array('-', '_'), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }
}
