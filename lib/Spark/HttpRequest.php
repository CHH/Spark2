<?php

namespace Spark;

class HttpRequest
{
    const GET    = "GET";
    const POST   = "POST";
    const PUT    = "PUT";
    const DELETE = "DELETE";    
    
    const SCHEME_HTTP  = 'http';
    const SCHEME_HTTPS = 'https';
    
    protected $method;
    protected $meta = array();
    protected $dispatched = false;
    
    protected $requestUri;
    
    function setMetadata($spec, $value = null)
    {
    	if (null === $value and is_array($spec)) {
			foreach ($spec as $key => $value) {
				$this->setMetadata($key, $value);
				return $this;
			}
    	}
        $this->meta[$spec] = $value;
        return $this;
    }
    
    function getMetadata($key)
    {
        if (!isset($this->meta[$key])) {
            return null;
        }
        return $this->meta[$key];
    }
    
    function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }
    
    function getMethod()
    {
        if ($this->method) return $this->method;
        
        $method = isset($_REQUEST["_method"]) 
            ? $_REQUEST["_method"] 
            : $this->server("REQUEST_METHOD");
        
        return $this->method = strtoupper($method);
    }
    
    function setRequestUri($requestUri)
    {
        $this->requestUri = $requestUri;
        return $this;
    }
    
    /**
     * Borrowed by Zend_Controller_Request_Http, thanks
     */
    function getRequestUri()
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
            
            // Set GET items, if available
            if (false !== ($pos = strpos($requestUri, '?'))) {
                // Get key => value pairs and set $_GET
                $query = substr($requestUri, $pos + 1);
                parse_str($query, $vars);
                $_GET = array_merge($_GET, $vars);
                
                $requestUri = substr($requestUri, 0, $pos);
            }
            
            $this->requestUri = $requestUri;
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
    
    function setQuery($spec, $value = null)
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
    
    function setPost($spec, $value = null)
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
    
    function setDispatched($dispatched = true)
    {
        $this->isDispatched = $dispatched;
        return $this;
    }
    
    function isDispatched()
    {
        return $this->isDispatched ? true : false;
    }
    
    function query($key = null, $default = null)
    {
        if (null === $key) return $_GET;
        else return isset($_GET[$key]) ? $_GET[$key] : $default;
    }
    
    function post($key = null, $default = null)
    {
        if (null === $key) return $_POST;
        else return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
    
    function env($key = null)
    {
        if (null === $key) return $_ENV;
        else return isset($_ENV[$key]) ? $_ENV[$key] : null;
    }
    
    function server($key = null) 
    {
        if (null === $key) return $_SERVER;
        else return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
    }
    
    function header($header)
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
    
    function isGet()
    {
        return self::GET == $this->getMethod();
    }
    
    function isPost()
    {
        return self::POST == $this->getMethod();
    }
    
    function isPut()
    {
        return self::PUT == $this->getMethod();
    }
    
    function isDelete()
    {
        return self::DELETE == $this->getMethod();
    }
    
    function isXmlHttpRequest()
    {
        return $this->header('X_REQUESTED_WITH') == 'XMLHttpRequest';
    }
}
