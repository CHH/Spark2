<?php

autoload("Spark\Http\NotFoundException", __DIR__ . "/Http/NotFoundException.php");
autoload("Spark\Http\FilterChain", __DIR__ . "/Http/FilterChain.php");

require_once "Http/RequestInterface.php";
require_once "Http/ResponseInterface.php";

require_once "Http/Header.php";
require_once "Http/Request.php";
require_once "Http/Response.php";
