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
    Spark\Util;

abstract class Base implements Dispatchable
{
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
     * Holds configurators per environment
     */
    protected $configurators = array();
    
    /**
     * Constructor
     */
    function __construct()
    {
        $this->settings   = new Settings;
        $this->response   = new Response;
        
        $this->init();
    }
    
    function configure($env, $callback = null)
    {
        if (null === $callback) {
            $callback = $env;
            $env = "_any";
        }
        
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("No callback given");
        }
        $this->configurators[$env] = $callback;
        return $this;
    }
    
    protected function bootstrap()
    {   
        $configurators = $this->configurators;
        $self = $this;
    
        $setup = function($env) use ($configurators, $self) {
            if (empty($configurators[$env])) {
                return;
            }
            call_user_func($configurators[$env], $self);
        };
    
        $env = $this->settings->get("environment") ?: "production";
        
        $setup("_any");
        $setup($env);
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
     * Template Method, use this to set up modular-style Apps
     */
    function init()
    {}

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

        if (empty($this->routes[$method])) return $this->response->setStatusCode(404);

        $match = false;

        foreach ($this->routes[$method] as $route) {
            try {
                $response = $route($request, $this->response);
                
                if (false === $response) continue;

                if ($response instanceof Response) $this->response = $response;
                $match = true;
                break;
            } catch (\Spark\PassException $e) {
                continue;
            }
        }

        ($match) ?: $this->response->setStatusCode(404);
    }

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

        $route = 
        function(Request $request, Response $previous = null) use ($pattern, $callback)
        {
            if (!preg_match($pattern, $request->getPathInfo(), $matches)) {
                return false;
            }
            $request->attributes->add(_\rest($matches));
            
            if (is_string($callback) and class_exists($callback)) {
                $callback = new $callback;
            }

            ob_start();
            $response = call_user_func($callback, $request, $previous);

            if (is_string($response)) {
                $response = new Response($response);
            }

            if ($response instanceof Response) {
                $response->write(ob_get_clean());
            } else {
                ob_end_clean();
            }
            return $response;
        };

        $this->routes[$verb]->push($route);
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
     * Registers an error handler
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
}
