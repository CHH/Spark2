<?php
/**
 * Error Object which contains references to the last request and the occured exception
 * 
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Spark
 * @package    Spark_App
 * @author     Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 * @copyright  Copyright (c) Christoph Hochstrasser
 * @license    MIT License
 */

namespace Spark;

use Spark\Http\Request;

class Error
{
    protected $msg;
    protected $exception;
    protected $request;
    
    function __construct($msg, Request $request, \Exception $exception = null)
    {
        $this->msg = $msg;
        $this->request = $request;
        $this->exception = $exception;
    }
    
    function getMessage()
    {
        return $this->msg;
    }
    
    function getRequest()
    {
        return $this->request;
    }
    
    function getException()
    {
        return $this->exception;
    }
}
