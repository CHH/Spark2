<?php

namespace Spark\Controller;

class HttpResponse
{
    protected $headers = array();
    protected $body = "";    
    
    protected $code = 200;
    
    public function setCode($code)
    {
    }
    
    public function header($header, $value)
    {
    }
    
    public function prepend($body)
    {
        $this->body = $body . $this->body;
        return $this;
    }
    
    public function append($body)
    {
        $this->body .= $body;
        return $this;
    }
    
    public function send()
    {
        foreach ($headers as $header) {
            // Output header
        }
        print $this->body;
    }
    
    public function toString()
    {
        ob_start();
        $this->send();
        return ob_get_clean();
    }
    
    public function __toString()
    {
        return $this->toString();
    }
}
