<?php
/**
 * The Command Resolver, resolves the request to a command Instance by 
 * module, command and action parameters
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_Controller
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark\Controller;

use Spark\Util\Options, Spark\HttpRequest;

/**
 * @category   Spark
 * @package    Spark_Controller
 * @copyright  Copyright (c) 2010 Christoph Hochstrasser
 * @license    MIT License
 */
class StandardResolver implements Resolver
{
    protected $_namingSpec = '\\{{module}}\\Application\\Controllers\\{{controller}}Controller';
    
    protected $_defaultControllerName = "Index";
    protected $_defaultModuleName     = null;
    
    protected $_controllerDirectory;
    protected $_moduleDirectory;
    protected $_moduleControllerDirectory = "controllers";

    public function __construct(Array $options = array())
    {
        if ($options) $this->setOptions($options);
    }

    public function setOptions(array $options)
    {
        Options::setOptions($this, $options);
        return $this;
    }

    public function getInstance(HttpRequest $request)
    { 
        if (null !== $request->getParam("module")) {
            $controller = $this->_loadCommand($request->getControllerName(), $request->getModuleName());
            return $controller;

        } else if (null !== $request->getParam("controller")) {
            $controller = $this->_loadCommand($request->getControllerName());
            return $controller;

        } else {
            return $this->_loadCommand($this->getDefaultControllerName());
        }
    }
    
    public function getControllerByName($controllerName = null, $moduleName = null)
    {
        return $this->_loadCommand($controllerName, $moduleName);
    }

    protected function _loadCommand($controllerName = null, $moduleName = null)
    {
        $controllerName = str_camelize($controllerName ?: $this->_defaultControllerName);
        $moduleName     = $moduleName ?: $this->_defaultModuleName;

        $className = $this->getClassName($controllerName, $moduleName);
        
        if ($moduleName) {
            $path = $this->getModuleDirectory() . DIRECTORY_SEPARATOR 
                  . str_camelize($moduleName) . DIRECTORY_SEPARATOR
                  . $this->getModuleControllerDirectory() . DIRECTORY_SEPARATOR 
                  . $controllerName . "Controller.php";
            
        } else {
            $path = $this->getControllerDirectory() . DIRECTORY_SEPARATOR
                  . $controllerName . "Controller.php";
        }
        
        $path = realpath($path);
        
        if (!$path) {
            return false;
        }
        
        include_once $path;
        
        if (!class_exists($className)) {
            throw new Exception("Class $className was not found in $path");
        }
        
        $controller = new $className;

        if (!$controller instanceof Controller) {
            return false;
        }
        return $controller;
    }

    public function setNamingSpec($namingSpec)
    {
        $this->_namingSpec = $namingSpec;
        return $this;
    }

    public function getControllerDirectory()
    {
        return $this->_controllerDirectory;
    }

    public function setControllerDirectory($controllerDirectory)
    {
        $this->_controllerDirectory = $controllerDirectory;
        return $this;
    }

    public function getDefaultControllerName()
    {
        return $this->_defaultControllerName;
    }

    public function setDefaultControllerName($controllerName)
    {
        $this->_defaultControllerName = $controllerName;
        return $this;
    }

    public function setDefaultModuleName($moduleName)
    {
        $this->_defaultModuleName = $moduleName;
        return $this;
    }

    public function getModuleDirectory()
    {
        return $this->_moduleDirectory;
    }

    public function setModuleDirectory($directory)
    {
        $this->_moduleDirectory = $directory;
        return $this;
    }

    public function getModuleControllerDirectory()
    {
        return $this->_moduleControllerDirectory;
    }

    public function setModuleControllerDirectory($directory)
    {
        $this->_moduleControllerDirectory = $directory;
        return $this;
    }

    protected function getClassName($controllerName, $moduleName = null)
    {
        $search  = array("{{module}}", "{{controller}}");
        $replace = array($moduleName, $controllerName);
        
        return str_replace($search, $replace, $this->_namingSpec);
    }
}
