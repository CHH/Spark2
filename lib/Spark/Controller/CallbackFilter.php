<?php

namespace Spark\Controller;

use Spark\Router\Filter as RouterFilter, 
    Spark\HttpRequest;

class CallbackFilter implements RouterFilter
{
    /** @var Resolver */
    protected $resolver;
    
    function __invoke(HttpRequest $request)
    {
        $resolver = $this->getResolver();
        $callback = $request->getMetadata("callback");
        
        if (!is_array($callback)) {
            return false;
        }
        
        $controller = array_delete_key("controller", $callback) 
            ?: $request->getMetadata("controller");
        
        $module = array_delete_key("module", $callback)
            ?: $request->getMetadata("module");
        
        $callback = $resolver->getControllerByName($controller, $module);
        
        if (false === $callback) {
            return false;
        }
        $request->setMetadata("callback", $callback);
    }

    function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

    function getResolver()
    {
        if (null === $this->resolver) {
            $this->resolver = new StandardResolver;
        }
        return $this->resolver;
    }
}
