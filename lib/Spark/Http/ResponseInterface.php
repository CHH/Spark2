<?php

namespace Spark\Http;

interface ResponseInterface
{
    function send();
    function sendHeaders();
    function sendContent();
    
    function addHeader($header, $content = null, $replace = false);
    function addHeaders(array $headers);
    
    function setContent($content = "");
    function getContent();
    
    function setStatus($status);
    function getStatus();
}
