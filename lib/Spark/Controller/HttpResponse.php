<?php

namespace Spark\Controller;

class HttpResponse
{
    protected $headers = array();
    protected $body = "";    
    
    protected $code = 200;
    
    function setCode($code)
    {
        $this->code = $code;
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
