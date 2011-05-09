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
        $this->request = ($request === null ? Request::createFromGlobals() : $request);
        
        // Run the environment's configurators
        $this->bootstrap();
    
        try {
            $this->runFilters("before");
            $this->dispatch($this->request);
            $this->runFilters("after");
            
        } catch (HaltException $e) {
            $this->response = $e->getResponse();
            
        } catch (\Exception $e) {
            $this->handleError(get_class($e), $e);
        }
        
        if (!$this->response->isSuccessful()) {
            $this->handleError($this->response->getStatusCode());
        }
        
        $this->runFilters("shutdown", array($this->request, $this->response));
        
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
        $method  = $request->getMethod();

        if (empty($this->routes[$method])) return $this->response->setStatusCode(404);

        $match = false;

        foreach ($this->routes[$method] as $route) {
            list($pattern, $callback, $constraints) = $route;
            
            // Match the pattern, when no match found then check the next route
            if (!preg_match($pattern, $request->getPathInfo(), $matches)) continue;

            // Eval all constraints for this route
            empty($constraints) ?: $this->evalConstraints($constraints, $request);

            unset($matches[0]);
            $request->attributes->add($matches);

            // If Callback is a class name then instantiate it
            if (is_string($callback) and class_exists($callback)) {
                $callback = new $callback;
            }
            
            try {
                ob_start();
                $response = call_user_func($callback, $request, $this->response);
                
                if ($response instanceof Response) $this->response = $response;
                
                $this->response->write(ob_get_clean());
                $match = true;
                break;
            } catch (\Spark\PassException $e) {
                continue;
            }
        }

        ($match) ?: $this->response->setStatusCode(404);
    }
    
    /**
     * Checks the route's constraints and invokes pass() if one constraint returns false
     *
     * @param  array   $constraints
     * @param  Request $request
     * @return bool
     */
    protected function evalConstraints(array $constraints, Request $request)
    {
        foreach ($constraints as $constraint) {
            if (!$constraint($request)) {
                pass();
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

    function GET($route, $constraints, $callback = null)
    {
        $this->head($route, $constraints, $callback);
        return $this->route("GET", $route, $constraints, $callback);
    }

    function POST($route, $constraints, $callback = null)
    {
        return $this->route("POST", $route, $constraints, $callback);
    }

    function PUT($route, $constraints, $callback = null)
    {
        return $this->route("PUT", $route, $constraints, $callback);
    }

    function DELETE($route, $constraints, $callback = null)
    {
        return $this->route("DELETE", $route, $constraints, $callback);
    }

    function HEAD($route, $constraints, $callback = null)
    {
        return $this->route("HEAD", $route, $constraints, $callback);
    }

    function OPTIONS($route, $constraints, $callback = null)
    {
        return $this->route("OPTIONS", $route, $constraints, $callback);
    }

    protected function route($verb, $route, $constraints, $callback)
    {
        // constraints were omitted and only the callback supplied
        if (is_callable($constraints)) {
            $callback = $constraints;
            $constraints = array();
        }

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Callback is not valid");
        }
        if (empty($this->routes[$verb])) {
            $this->routes[$verb] = new \SplStack;
        }

        $exp = new Util\StringExpression($route);
        $pattern = $exp->toRegExp();

        $constraints = $this->parseConstraints($constraints);

        $this->routes[$verb]->push(array($pattern, $callback, $constraints));
    }

    protected function parseConstraints(array $constraints)
    {
        $compiled = array();

        foreach ($constraints as $constraint => $args) {
            if (is_callable(array($this, _\camelize($constraint, false)))) {
                $constraint = array($this, _\camelize($constraint, false));

            } else if ($this->settings->get($constraint)) {
                $constraint = $this->settings->get($constraint);
            }
            $compiled[] = call_user_func($constraint, $args);
        }

        return $compiled;
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
}
