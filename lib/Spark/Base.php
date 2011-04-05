<?php
/**
 * Base of the modular style DSL
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Base
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

use Spark\Http\Request,
    Spark\Http\Response,
    Spark\Util,
    Underscore as _;

abstract class Base
{
    /** @var \Spark\Util\ExtensionManager */
    public $extensions;

    /** @var \Spark\Settings */
    public $settings;

    /** @var Request */
    public $request;

    /** @var Response */
    public $response;
    
    /**
     * Routes for each HTTP Method
     *
     * @var array
     */
    protected $routes = array();

    /** @var array */
    protected $filters = array();

    /** Error Handlers */
    protected $error = array();

    /**
     * Constructor
     */
    function __construct()
    {
        $this->settings   = new Settings;
        $this->extensions = new Util\ExtensionManager($this);
        $this->response   = new Response;
        
        $this->register("\Spark\Extension\Templates");
        $this->register("\Spark\Extension\Redirecting");
    
        $this->init();
    }

    /**
     * Adds a filter to the specified queue
     *
     * @param  string $queue The queue name
     * @param  callback $handler
     * @return App
     */
    protected function addFilter($queue, $handler)
    {
        if (empty($this->filters[$queue])) {
            $this->filters[$queue] = new \SplQueue;
        }
        $this->filters[$queue]->enqueue($handler);
        return $this;
    }

    /**
     * Runs the queue's filters
     *
     * @param  string $queue Queue name
     * @param  array $args Arguments to pass to the filter
     * @return bool
     */
    protected function runFilters($queue, array $args = array())
    {
        if (empty($this->filters[$queue])) {
            return false;
        }

        foreach ($this->filters[$queue] as $filter) {
            $response = call_user_func_array($filter, $args);
            
            (!$response instanceof Response) ?: $this->response = $response;
        }
        return true;
    }

    /**
     * Template Method which can be used to initialize Subclasses
     */
    function init()
    {}

    /**
     * Dispatches the request and sends the Response
     *
     * @param  Request  $request
     * @return Response Returns the response if "return_response" is TRUE
     */
    function run(Request $request = null)
    {
        $this->request = ($request === null ? Request::createFromGlobals() : $request);

        try {
            $this->runFilters("before", array($this));
            $this->dispatch();
            $this->runFilters("after", array($this));
            
            if (!$this->response->isSuccessful()) throw new \Exception("Not successful");
        } catch (HaltException $e) {
            $this->response = $e->getResponse();
            
        } catch (\Exception $e) {
            $this->handleError(get_class($e), $e);
        }
        
        if (!$this->response->isSuccessful()) {
            $this->handleError($this->response->getStatusCode());
        }
        
        $this->runFilters("shutdown", array($this));
        
        if (true === $this->settings->get("send_response")) {
            $this->response->send();
        }
        
        return $this->response;
    }
    
    /**
     * Dispatches the request
     */
    protected function dispatch()
    {
        $request = $this->request;
        $method  = $request->getMethod();

        if (empty($this->routes[$method])) return $this->response->setStatusCode(404);

        $match = false;

        foreach ($this->routes[$method] as $route) {
            list($pattern, $callback, $conditions) = $route;
            
            // Match the pattern, when no match found then check the next route
            if (!preg_match($pattern, $request->getRequestUri(), $matches)) continue;

            // Eval all conditions for this route
            empty($conditions) ?: $this->evalConditions($conditions, $request);

            unset($matches[0]);
            $request->attributes->add($matches);

            // If Callback is a class name then instantiate it
            if (is_string($callback) and class_exists($callback)) {
                $callback = new $callback;
            }
            
            try {
                $response = call_user_func($callback, $this);
                
                if ($response instanceof Response) $this->response = $response;
                
                $match = true;
                break;
            } catch (\Spark\PassException $e) {
                continue;
            }
        }

        ($match) ?: $this->response->setStatusCode(404);
    }
    
    /** 
     * Checks the route's conditions and invokes pass() if one condition returns false
     *
     * @param  array   $conditions
     * @param  Request $request
     * @return bool
     */
    protected function evalConditions(array $conditions, Request $request)
    {
        foreach ($conditions as $condition) {
            if (!$condition($request)) {
                $this->pass();
            }
        }
        return true;
    }

    protected function handleError($code = "\Exception", \Exception $exception = null)
    {
        $handler = empty($this->errors[$code]) ? null : $this->errors[$code];

        if (!$handler) {
            if (null === $exception) {
                throw $exception;
            }
            return false;
        }
        
        $response = call_user_func($handler, $this, $exception);
        
        if ($response instanceof Response) {
            $this->response = $response;
        }
        return true;
    }

    /*
     * Methods for defining handlers for HTTP Methods
     */

    function GET($route, $conditions, $callback = null)
    {
        $this->head($route, $conditions, $callback);
        return $this->route("GET", $route, $conditions, $callback);
    }

    function POST($route, $conditions, $callback = null)
    {
        return $this->route("POST", $route, $conditions, $callback);
    }

    function PUT($route, $conditions, $callback = null)
    {
        return $this->route("PUT", $route, $conditions, $callback);
    }

    function DELETE($route, $conditions, $callback = null)
    {
        return $this->route("DELETE", $route, $conditions, $callback);
    }

    function HEAD($route, $conditions, $callback = null)
    {
        return $this->route("HEAD", $route, $conditions, $callback);
    }

    function OPTIONS($route, $conditions, $callback = null)
    {
        return $this->route("OPTIONS", $route, $conditions, $callback);
    }

    protected function route($verb, $route, $conditions, $callback)
    {
        // Conditions were omitted and only the callback supplied
        if (is_callable($conditions)) {
            $callback = $conditions;
            $conditions = array();
        }

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Callback is not valid");
        }
        if (empty($this->routes[$verb])) {
            $this->routes[$verb] = new \SplStack;
        }

        $exp = new Util\StringExpression($route);
        $pattern = $exp->toRegExp();

        $conditions = $this->parseConditions($conditions);

        $this->routes[$verb]->push(array($pattern, $callback, $conditions));
    }

    /**
     * Call extensions
     */
    function __call($method, array $args)
    {
        return $this->extensions->call($method, $args);
    }

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
     * Sets an option
     *
     * @param  string|array $spec Either list of key-values or name of the key
     * @param  mixed $value
     * @return App
     */
    function set($spec, $value = null)
    {
        $this->settings->set($spec, $value);
        return $this;
    }

    /**
     * Set the given Setting to TRUE
     *
     * @param string $setting
     * @return App
     */
    function enable($setting)
    {
        $this->settings->enable($setting);
        return $this;
    }

    /**
     * Set the given Setting to FALSE
     *
     * @param string $setting
     */
    function disable($setting)
    {
        $this->settings->disable($setting);
        return $this;
    }

    protected function parseConditions(array $conditions)
    {
        $compiled = array();

        foreach ($conditions as $condition => $args) {
            if (is_callable(array($this, _\camelize($condition, false)))) {
                $condition = array($this, _\camelize($condition, false));

            } else if ($this->settings->get($condition)) {
                $condition = $this->settings->get($condition);
            }
            $compiled[] = call_user_func($condition, $args);
        }

        return $compiled;
    }

    /**
     * Registers an extension for the DSL
     *
     * @see ExtensionManager
     * @param object $extension,...
     */
    function register(/* $extension,... */)
    {
        foreach (func_get_args() as $extension) {
            $this->extensions->register($extension);
        }
        return $this;
    }

    /**
     * Attaches a filter to the filters run before dispatching
     *
     * @param  object $filter Callable object (Closure or Object implementing __invoke)
     * @return App
     */
    function before($handler)
    {
        return $this->addFilter("before", $handler);
    }

    /**
     * Attaches a filter to the filters run after dispatching
     *
     * @param  object $filter Callable object (Closure or Object implementing __invoke)
     * @return App
     */
    function after($handler)
    {
        return $this->addFilter("after", $handler);
    }
    
    function shutdown($handler)
    {
        return $this->addFilter("shutdown", $handler);
    }
    
    /**
     * Registers an error handler
     */
    function error($code = "\Exception", $handler = null)
    {
        if (is_callable($code)) {
            $handler = $code;
            $code = "\Exception";
        }
        if (!is_array($code)) {
            $code = array($code);
        }
        foreach ($code as $c) {
            $this->errors[$c] = $handler;
        }
        return $this;
    }

    /**
     * Registers an handler on the error code 404
     *
     * @param  callback $callback
     * @return App
     */
    function notFound($callback)
    {
        return $this->error(404, $callback);
    }

    /*
     * Bundled Route Conditions
     */

    function hostName($pattern)
    {
        return function(Request $request) use ($pattern) {
            $hostname = $request->getHost();
            return preg_match($pattern, $hostname, $matches) > 0;
        };
    }

    function userAgent($pattern)
    {
        return function(Request $request) use ($pattern) {
            $userAgent = $request->headers->get("user-agent");
            return preg_match($pattern, $userAgent, $matches) > 0;
        };
    }

    /**
     * Returns a matcher which returns true if the client accepts the format
     *
     * @param  string $format,... One or more formats, which the client should accept
     * @return bool
     */
    function provides($format)
    {
        $formats = func_get_args();

        return function(Request $request) use ($formats) {
            return _\chain($request->getAcceptableContentTypes())
                ->map(array($request, "getFormat"))
                ->select(function($value) use ($formats) {
                    return in_array($value, $formats);
                })
                ->value() ? true : false;
        };
    }
}
