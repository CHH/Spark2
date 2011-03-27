<?php

namespace Spark;

use Phar;

function print_progress()
{
    print ".";
}

function create_phar($distFile = "Spark.phar")
{
    $distPath = __DIR__ . "/dist/" . $distFile;
    
    if (file_exists($distPath)) {
        unlink($distPath);
    }
    
    @mkdir(__DIR__ . "/dist");
    
    print "Creating PHAR in $distPath...";
    
    $phar = new Phar($distPath, 0, "Spark");
    $phar->setSignatureAlgorithm(Phar::SHA1);
    $phar->startBuffering();
    
    print_progress();
    
    $files = array_merge(find_files("lib"), find_files("vendor"));
    
    print_progress();
    
    foreach ($files as $file) {
        add_file($phar, $file);
    }
    
    add_file($phar, __DIR__ . "/LICENSE.txt");
    //add_file($phar, __DIR__ . "/_autoload.php");
    
    $phar["_autoload.php"] = get_classmap_autoloader($files);
    
    $stub = <<<STUB
<?php
/**
 * This file is part of the Spark Web Framework
 *
 * @copyright (c) 2011 Christoph Hochstrasser
 * @license MIT License
 */

require_once __DIR__ . "/lib/Spark.php";

__HALT_COMPILER();
STUB;
    
    $phar["_cli_stub.php"] = $stub;
    $phar["_web_stub.php"] = $stub;
    
    $phar->setDefaultStub('_cli_stub.php', '_web_stub.php');
    $phar->stopBuffering();
    
    print "Finished.\n";
}

function get_classmap_autoloader(array $files)
{
    $map = array();

    foreach ($files as $file) {
        $file = str_replace(__DIR__, '', $file);
        
        $class = str_replace(array("/vendor", "/lib"), '', $file);
        $class = str_replace('/', '\\', $class);
        $class = substr($class, 0, -4);
        $class = ltrim($class, '\\');
        $map[$class] = $file;
    }
    
    $map = var_export($map, true);
    
    $code = <<<'EOL'
<?php
$map = %s;
spl_autoload_register(function($class) use ($map) {
    if (isset($map[$class])) {
        require_once __DIR__ . $map[$class];
    }
}); 
EOL;
    
    return sprintf($code, $map);
}

function find_files($dir)
{
    $files = array();

    $iterator = new \RecursiveDirectoryIterator(__DIR__ . "/$dir");
    
    foreach (new \RecursiveIteratorIterator($iterator) as $file) {
        if (!$file->isFile() or substr($file->getFileName(), -4, 4) !== ".php") {
            continue;
        }
        $files[] = (string) $file;
    }
    
    return $files;
}

function add_file(Phar $phar, $file)
{
    $pharPath = str_replace(__DIR__, '', $file);
    $content = file_get_contents($file);
    
    $phar->addFromString($pharPath, $content);
    print_progress();
}

$out = isset($argv[1]) ? $argv[1] : "Spark.phar";
create_phar($out);
