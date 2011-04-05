<?php

namespace Spark;

use Phar;

class PharCompiler
{
    protected $classMap = array();

    function run($distFile = "spark.phar")
    {
        $this->createPhar($distFile);
    }

    protected function printProgress()
    {
        print ".";
    }

    protected function createPhar($distFile = "spark.phar")
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

        $this->printProgress();

        $files = array_merge($this->findFiles("lib"), $this->findFiles("vendor"));

        $this->printProgress();

        foreach ($files as $file) {
            $this->addFile($phar, $file);
        }

        $this->addFile($phar, __DIR__ . "/LICENSE.txt");
        //add_file($phar, __DIR__ . "/_autoload.php");

        $phar["_autoload.php"] = $this->getClassmapAutoloader();

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

    /**
     * Generates an autoloader for the files in the class map
     *
     * @return string PHP Code
     */
    function getClassmapAutoloader()
    {
        $map = var_export($this->classMap, true);

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

    /**
     * Returns all PHP Files in $dir
     *
     * @param string $dir
     * @return array
     */
    function findFiles($dir)
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

    /**
     * Adds contents of the supplied file to the PHAR
     *
     * @param string $file
     */
    protected function addFile(Phar $phar, $file)
    {
        $pharPath = str_replace(__DIR__, '', $file);

        $content = file_get_contents($file);

        if (".php" == substr($file, -4, 4)) {
            $content = $this->stripComments($content);
            $class = $this->findClass($content);

            if ($class) {
                // Register symbol in class map for autoloader generation
                $this->classMap[$class] = $pharPath;
            }
        }

        $phar->addFromString($pharPath, $content);

        $this->printProgress();
    }

    /**
     * Looks in the supplied content for a class definition
     *
     * @param string $content
     * @return bool|string Returns the fully-qualified name of the class if found
     */
    protected function findClass($content)
    {
        $namespace = "/namespace ([a-zA-Z0-9_\\\\]+);/";

        if (preg_match($namespace, $content, $matches)) {
            $currentNs = $matches[1];
        }

        $class = "/(class|interface) ([a-zA-Z0-9_\\\\]+)/";

        if (preg_match($class, $content, $matches)) {
            $symbol = (empty($currentNs) ? '' : $currentNs . '\\') . $matches[2];
            return $symbol;
        }
        return false;
    }

    /**
     * Strips all comments from the supplied string of PHP code
     *
     * @param string $code
     */
    protected function stripComments($code)
    {
        $newStr = '';

        $commentTokens = array(T_COMMENT, T_DOC_COMMENT);

        $tokens = token_get_all($code);

        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (in_array($token[0], $commentTokens))
                    continue;

                $token = $token[1];
            }

            $newStr .= $token;
        }

        return $newStr;
    }
}

$dest = isset($argv[1]) ? $argv[1] : "spark.phar";

$compiler = new PharCompiler;
$compiler->run($dest);

