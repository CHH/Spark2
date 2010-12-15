<?php

namespace Spark\Controller;

class HttpRequest
{
    const HTTP_GET    = "GET";
    const HTTP_POST   = "POST";
    const HTTP_PUT    = "PUT";
    const HTTP_DELETE = "DELETE";    
    
    const SCHEME_HTTP  = 'http';
    const SCHEME_HTTPS = 'https';
    
    protected $method;
    protected $params = array();
    protected $dispatched = false;
    
    protected $requestUri;
    
    public function __construct()
    {}
    
    public function setParam($param, $value)
    {
        $this->params[$param] = $value;
        return $this;
    }
    
    public function getUserParam($param, $default = null)
    {
        if (!isset($this->params[$param])) {
            return $default;
        }
        return $this->params[$param];
    }
    
    public function getParam($param, $default = null)
    {
        if (isset($this->params[$param])) {
            return $this->params[$param];
        } else if (isset($_REQUEST[$param])) {
            return $_REQUEST[$param];
        }
        return $default;
    }
    
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }
    
    public function getMethod()
    {
        if ($this->method) return $this->method;
        
        if ($method = $this->getParam("_method")) {
            $this->method = strtoupper($method);
        } else {
            $this->method = $this->server("REQUEST_METHOD");
        }
        return $this->method;
    }
    
    public function setRequestUri($requestUri)
    {
        $this->requestUri = $requestUri;
        return $this;
    }
    
    /**
     * Borrowed by Zend_Controller_Request_Http, thanks
     */
    public function getRequestUri()
    {
        if ($this->requestUri === null) {
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
                $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
            } elseif (
                // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
                isset($_SERVER['IIS_WasUrlRewritten'])
                && $_SERVER['IIS_WasUrlRewritten'] == '1'
                && isset($_SERVER['UNENCODED_URL'])
                && $_SERVER['UNENCODED_URL'] != ''
                ) {
                $requestUri = $_SERVER['UNENCODED_URL'];
            } elseif (isset($_SERVER['REQUEST_URI'])) {
                $requestUri = $_SERVER['REQUEST_URI'];
                // Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
                $schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();
                if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                    $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
                }
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
                $requestUri = $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $requestUri .= '?' . $_SERVER['QUERY_STRING'];
                }
            } else {
                return;
            }
            
            $this->requestUri = $requestUri;
            
            // Set GET items, if available
            if (false !== ($pos = strpos($requestUri, '?'))) {
                // Get key => value pairs and set $_GET
                $query = substr($requestUri, $pos + 1);
                parse_str($query, $vars);
                $_GET = array_merge($_GET, $vars);
            }
        }
        return $this->requestUri;
    }
    
    function getHttpHost()
    {
        $host = $this->server('HTTP_HOST');
        if (!empty($host)) {
            return $host;
        }

        $scheme = $this->getScheme();
        $name   = $this->server('SERVER_NAME');
        $port   = $this->server('SERVER_PORT');

        if(null === $name) {
            return '';
        }
        elseif (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443)) {
            return $name;
        } else {
            return $name . ':' . $port;
        }
    }
    
    function getScheme()
    {
        return ($this->server('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
    }
    
    public function setQuery($spec, $value = null)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $this->setQuery($key, $value);
            }
            return $this;
        }
        $_GET[$spec] = $value;
        return $this;
    }
    
    public function query($key = null, $default = null)
    {
        if (null === $key) return $_GET;
        
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }
    
    public function setPost($spec, $value = null)
    {
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $this->setPost($key, $value);
            }
            return $this;
        }
        $_POST[$key] = $value;
        return $this;
    }
    
    public function post($key = null, $default = null)
    {
        if (null === $key) return $_POST;
        
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
    
    public function env($key = null)
    {
        if (null === $key) return $_ENV;
        
        return isset($_ENV[$key]) ? $_ENV[$key] : null;
    }
    
    public function server($key = null) 
    {
        if (null === $key) return $_SERVER;
        
        return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
    }
    
    public function header($header)
    {
        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }

        return false;
    }
    
    public function setDispatched($dispatched = true)
    {
        $this->isDispatched = $dispatched;
        return $this;
    }
    
    public function isDispatched()
    {
        return $this->isDispatched ? true : false;
    }
    
    public function isGet()
    {
        return self::HTTP_GET == $this->getMethod();
    }
    
    public function isPost()
    {
        return self::HTTP_POST == $this->getMethod();
    }
    
    public function isPut()
    {
        return self::HTTP_PUT == $this->getMethod();
    }
    
    public function isDelete()
    {
        return self::HTTP_DELETE == $this->getMethod();
    }
    
    public function isXmlHttpRequest()
    {
        return $this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest';
    }
}
