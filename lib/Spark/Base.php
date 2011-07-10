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
    Spark\Settings,
    Spark\Util;

abstract class Base implements Dispatchable
{
    /** @var Settings */
    public $settings;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;
    
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
     * Holds configurators per environment
     */
    protected $configurators = array();

    /**
     * Map of extension methods
     */
    protected $extensions = array();

    /**
     * Constructor
     */
    function __construct()
    {
        $this->settings = new Settings;
        $this->response = new Response;
        
        $this->init();
    }
    
    /**
     * Template Method, use this to set up modular-style Apps
     */
    function init()
    {}

    /**
     * Defines a callback which should get run on dispatch loop startup
     *
     * @param  string|callback $env If this is a string, then it's treated
     * as Environment Name (e.g. Production), if a callback is given, then this
     * callback is run under every environment
     *
     * @param  callback $callback
     * @return Base
     */
    function configure($env, $callback = null)
    {
        if (null === $callback) {
            $callback = $env;
            $env = "_any";
        }
        
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(
                "configure() expects a valid callback, none given."
            );
        }
        $this->configurators[$env] = $callback;
        return $this;
    }
    
    /**
     * Calls all callbacks defined by {@see configure()} at startup
     */
    protected function bootstrap()
    {   
        $configurators = $this->configurators;
        $env = $this->settings->get("environment") ?: "production";

        foreach (array('_any', $env) as $e) {
            if (empty($configurators[$e])) {
                continue;
            }
            call_user_func($configurators[$e], $this);
        }
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
        if (empty($args)) {
            $args = array($this->request, $this->response);
        }
    
        if (empty($this->filters[$queue])) {
            return false;
        }
        
        foreach ($this->filters[$queue] as $filter) {
            ob_start();
            $return = call_user_func_array($filter, $args);
            (!$return instanceof Response) ?: $this->response = $return;

            $this->response->write(ob_get_clean());
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    function __invoke(Request $request, Response $previous = null)
    {
        if (null !== $previous) {
            $this->response = $previous;
        }
        return $this->run($request);
    }
    
    /**
     * Dispatches the request and sends the Response
     *
     * @param  Request  $request
     * @return Response Returns the response if "return_response" is TRUE
     */
    function run(Request $request = null)
    {
        $this->request = $request ?: Request::createFromGlobals();
        
        // Run the environment's configurators
        $this->bootstrap();
        
        try {
            $this->runFilters("before");
            $this->dispatch($this->request);
            $this->runFilters("after");
        
        // Send the Halt Exception's Response
        } catch (HaltException $e) {
            $this->response = $e->getResponse();
            
        } catch (\Exception $e) {
            $this->handleError(get_class($e), $e);
        }
        
        if (!$this->response->isSuccessful()) {
            $this->handleError($this->response->getStatusCode());
        }
        
        // Run the shutdown filters, implement layouts there
        $this->runFilters("shutdown");
        
        if (true === $this->settings->get("send_response")) {
            $this->response->send();
        }
        
        return $this->response;
    }
    
    /**
     * Finds the matching route for the request URI and dispatches the request to
     * the defined handler
     */
    protected function dispatch(Request $request)
    {
        $method = $request->getMethod();

        if (empty($this->routes[$method])) {
            return $this->response->setStatusCode(404);
        }

        $match = false;

        foreach ($this->routes[$method] as $route) {
            list($pattern, $callback) = $route;

            try {
                if (!preg_match($pattern, $request->getPathInfo(), $matches)) {
                    continue;
                }
                $request->attributes->add(array_tail($matches));
                
                if (is_string($callback) and class_exists($callback)) {
                    $callback = new $callback;
                }

                ob_start();
                $response = call_user_func($callback, $request, $this->response);

                if (is_string($response)) {
                    $response = new Response($response);
                }

                if ($response instanceof Response) {
                    $response->write(ob_get_clean());
                } else {
                    ob_end_clean();
                }
                
                if (false === $response) continue;

                if ($response instanceof Response) {
                    $this->response = $response;
                }
                $match = true;
                break;

            } catch (\Spark\PassException $e) {
                continue;
            }
        }

        ($match) ?: $this->response->setStatusCode(404);
    }

    /**
     * Called to handle an Error
     *
     * @param string $code HTTP Error Code or Class Name of Exception
     *                     which should get handled
     * @param \Exception $exception The Exception Instance, if 
     *                              an Exception was caught
     *
     * @return bool TRUE if the Error was handled, FALSE if not
     */
    protected function handleError($code = "Exception", \Exception $exception = null)
    {
        $handler = empty($this->errors[$code]) ? null : $this->errors[$code];

        if (!$handler) {
            if (null !== $exception) {
                throw $exception;
            }
            return false;
        }
        
        ob_start();
        $response = call_user_func($handler, $this->request, $this->response, $exception);
        
        if ($response instanceof Response) {
            $this->response = $response;
        }
        $this->response->write(ob_get_clean());
        
        return true;
    }

    /*
     * Methods for defining handlers for HTTP Methods
     */

    function get($route, $callback = null)
    {
        $this->head($route, $callback);
        return $this->route("GET", $route, $callback);
    }

    function post($route, $callback = null)
    {
        return $this->route("POST", $route, $callback);
    }

    function put($route, $callback = null)
    {
        return $this->route("PUT", $route, $callback);
    }

    function delete($route, $callback = null)
    {
        return $this->route("DELETE", $route, $callback);
    }

    function head($route, $callback = null)
    {
        return $this->route("HEAD", $route, $callback);
    }

    function options($route, $callback = null)
    {
        return $this->route("OPTIONS", $route, $callback);
    }

    /**
     * Connects the callback with the HTTP Method and Route Pattern
     *
     * @param string $verb  HTTP Method (GET, POST, PUT,...)
     * @param string $route Path with Variables, e.g. /users/:id
     * @param callback $callback
     * 
     * @return void
     */
    protected function route($verb, $route, $callback)
    {
        if (!is_callable($callback) and !class_exists($callback)) {
            throw new \InvalidArgumentException("Callback is not valid");
        }
        if (empty($this->routes[$verb])) {
            $this->routes[$verb] = new \SplStack;
        }
        
        $exp = new Util\StringExpression($route);
        $pattern = $exp->toRegExp();

        $this->routes[$verb]->push(array($pattern, $callback));
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

    /**
     * Attaches a filter to the filters run before dispatching
     *
     * @param  callback $handler
     * @return Base
     */
    function before($handler)
    {
        return $this->addFilter("before", $handler);
    }

    /**
     * Attaches a filter to the filters run after dispatching
     *
     * @param  callback $handler
     * @return Base
     */
    function after($handler)
    {
        return $this->addFilter("after", $handler);
    }
    
    /**
     * Attach a handler which gets run just before the response gets sent
     *
     * @param callback $handler
     * @return Base
     */
    function shutdown($handler)
    {
        return $this->addFilter("shutdown", $handler);
    }
    
    /**
     * Registers an error handler on the given code or
     * Exception Class
     *
     * @param mixed $code
     */
    function error($code = "Exception", $handler = null)
    {
        if (is_callable($code)) {
            $handler = $code;
            $code = "Exception";
        }
        $code = (array) $code;
        foreach ($code as $c) {
            if (is_string($c) and class_exists($c)) {
                $c = ltrim($c, "\\");
            }
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

    function register($extension)
    {
        if (is_string($extension) and class_exists($extension)) {
            $extension = new $extension;
        }

        if (!is_object($extension)) {
            throw new \InvalidArgumentException(sprintf(
                "Extension must be an object, %s given", gettype($extension)
            ));
        }

        foreach (get_class_methods($extension) as $method) {
            if (!str_starts_with('__', $method) and 'registered' != $method) {
                $this->extensions[$method] = array($extension, $method);
            }
        }

        if (method_exists($extension, 'registered')) {
            $extension->registered($this);
        }
        return $this;
    }

    function __call($extension, array $args)
    {
        if (!isset($this->extensions[$extension])) {
            throw new \BadMethodCallException(sprintf(
                "Undefined Method %s", $extension
            ));
        }
        return call_user_func_array($this->extensions[$extension], $args);
    }
}
