<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 14/09/17
 * Time: 11:21
 */

namespace Dcp\Style;

use Leafo\ScssPhp\Server;
use Leafo\ScssPhp\Compiler;

class dcpScssParser implements ICssParser
{
    protected $_srcFiles = null;
    protected $_styleConfig = array();
    protected $_options = array();
    /**
     * @param string|string[] $srcFiles    path or array of path of source file(s) relative to server root
     * @param array           $options     array of options
     * @param array           $styleConfig full style configuration
     */
    public function __construct($srcFiles, Array $options, Array $styleConfig)
    {
        if (is_array($srcFiles)) {
            $this->_srcFiles = $srcFiles;
        } else {
            $this->_srcFiles = array(
                $srcFiles
            );
        }
        $cacheDir = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'scss';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }
        $this->_options = $options;
        $this->_options['cache_dir'] = $cacheDir;
        $this->_options['cache_method'] = 'serialize';
        $this->_options['sourceMapBasepath'] = DEFAULT_PUBDIR;
        $this->_styleConfig = $styleConfig;
    }
    /**
     * @param string $destFile destination file path relative to server root (if null, parsed result is returned)
     *
     * @return void
     * @throws \Exception
     */
    public function gen($destFile = null)
    {
        $fullTargetPath = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . $destFile;
        $fullTargetDirname = dirname($fullTargetPath);
        if (!is_dir($fullTargetDirname) && (false === mkdir($fullTargetDirname, 0777, true))) {
            throw new Exception("STY0005", "$fullTargetDirname dir could not be created for file $destFile");
        }

        $autoloadFuncs = spl_autoload_functions();
        foreach ($autoloadFuncs as $unregisterFunc) {
            //spl_autoload_unregister($unregisterFunc);
        }

        $exception = null;
        try {
            $compiler = new Compiler();
            $server = new Server(DEFAULT_PUBDIR, $this->_options['cache_dir'], $compiler);
            $compiler->addImportPath(function ($path) {
               error_log("Query path : $path");
               return null;
            });
            foreach ($this->_srcFiles as $srcPath) {
                $srcFullPath = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . $srcPath;

                error_log("Dirname=".dirname($srcFullPath));
                error_log("Fullpath=$srcFullPath");

                $compiler->addImportPath(dirname($srcFullPath));
                $css = $server->checkedCachedCompile($srcFullPath, $fullTargetPath);
                if (false === file_put_contents($fullTargetPath, $css)) {
                    throw new Exception("STY0005", "$fullTargetPath could not be written for file $destFile");
                }
            }
        }
        catch(\Exception $e) {
            $exception = $e;
        }

        foreach ($autoloadFuncs as $registerFunc) {
            // spl_autoload_register($registerFunc);
        }

        if ($exception !== null) {
            throw $exception;
        }
    }
}