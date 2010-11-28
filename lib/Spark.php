<?php

namespace Spark;

const LIB_PATH = realpath(__DIR__ . DIRECTORY_SEPARATOR . "Spark");

$loader = new Autoloader(array("include_path" => LIB_PATH));
$loader->register();
