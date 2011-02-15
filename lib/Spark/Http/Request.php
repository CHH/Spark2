<?php
/**
 * A class which represents the incoming HTTP Request
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

class Request
{
    const GET     = "GET";
    const POST    = "POST";
    const PUT     = "PUT";
    const DELETE  = "DELETE";    
    const HEAD    = "HEAD";
    const OPTIONS = "OPTIONS";
    
    const SCHEME_HTTP  = 'http';
    const SCHEME_HTTPS = 'https';
    
    protected $method;
    protected $meta = array();
    protected $dispatched = false;
    
    protected $requestUri;
    protected $callback;
    
    function params()
    {
        return $this->meta + $_REQUEST + $_FILES + $_COOKIE;
    }
    
    function param($key)
    {
        if (!is_string($key) or empty($key)) {
            throw new InvalidArgumentException("Key must be a non-empty string");
        }
        
        switch (true) {
            case $value = $this->meta($key):
                break;
            case $value = $this->query($key):
                break;
            case $value = $this->post($key);
                break;
            case $value = $this->file($key);
                break;
            default:
                $value = null;
                break;
        }
        return $value;
    }
    
    /**
     * Set application metadata
     *
     * @param  string|array $spec Either key, a list of key-values, or null (returns all metadata)
     * @param  mixed $value
     * @return Request
     */
    function meta($spec = null, $value = null)
    {
        if (null === $spec) {
            return $this->meta;
        }
        if (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $this->meta[$key] = $value;
            }
            return $this;
        }
        if (null === $value) {
            return isset($this->meta[$spec]) ? $this->meta[$spec] : null;
        }
        $this->meta[$spec] = $value;
        return $this;
    }

    function setDispatched($dispatched = true)
    {
        $this->dispatched = $dispatched;
        return $this;
    }
    
    function isDispatched()
    {
        return $this->dispatched ? true : false;
    }
    
    function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }
    
    function getCallback()
    {
        return $this->callback;
    }

    /* capabilities */
    function getETags()
    {
    }

    function getLanguages()
    {
    }

    function getPreferredLanguage(array $locales = null)
    {
    }

    function getCharsets()
    {
    }

    function getAcceptableContentTypes()
    {
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
     * @copyright Copyright (c) 2005-2010 Zend Technologies USA Inc.
     * @license   http://framework.zend.com/license/new-bsd New BSD License
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
    
    /**
     * @copyright Copyright (c) 2005-2010 Zend Technologies USA Inc.
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     */
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

    /**
     * @copyright Copyright (c) 2005-2010 Zend Technologies USA Inc.
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     */
    function getScheme()
    {
        return ($this->server('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
    }

    function getPort()
    {
        return $this->server("SERVER_PORT");
    }

    function getPathInfo()
    {
        return $this->server("PATH_INFO");
    }

    function getBasePath()
    {
    }

    function getBaseUrl()
    {
    }

    function getScriptName()
    {
    }

    function getUri()
    {
    }
    
    /* Superglobals */
    function query($key = null)
    {
        if (null === $key) return $_GET;
        else return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    function cookie($key = null)
    {
        if (null === $key) return $_COOKIE;
        else return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }

    function file($key = null)
    {
        if (null === $key) return $_FILES;
        else return isset($_FILES[$key]) ? $_FILES[$key] : null;
    }

    function post($key = null)
    {
        if (null === $key) return $_POST;
        else return isset($_POST[$key]) ? $_POST[$key] : null;
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

    function body()
    {
    }
    
    /**
     * @copyright Copyright (c) 2005-2010 Zend Technologies USA Inc.
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     */
    function header($key)
    {
        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$key])) {
                return $headers[$key];
            }
        }
        return false;
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

    function hasFormData()
    {
    }
    
    /* Test for HTTP Method */
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

    function isHead()
    {
        return self::HEAD == $this->getMethod();
    }

    function isOptions()
    {
        return self::OPTIONS == $this->getMethod();
    }
    
    function isXmlHttpRequest()
    {
        return (bool) $this->header('X_REQUESTED_WITH');
    }

    function isSecure()
    {
        return "https" == $this->getScheme();
    }

    function isNoCache()
    {
    }

    function isFlashRequest()
    {
    }
}
