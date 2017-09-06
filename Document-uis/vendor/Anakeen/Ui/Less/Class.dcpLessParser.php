<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Style;


class dcpLessParser implements ICssParser
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
        $cacheDir = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'less';
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
            $less_files = array();
            foreach ($this->_srcFiles as $srcPath) {
                $srcFullPath = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . $srcPath;
                $less_files[$srcFullPath] = '';
            }
            $css_file_name = \Less_Cache::Get($less_files, $this->_options, isset($this->_styleConfig["sty_const"]["less_var"]) ? $this->_styleConfig["sty_const"]["less_var"] : array());
            $css = file_get_contents($this->_options['cache_dir'] . DIRECTORY_SEPARATOR . $css_file_name);
            if (false === file_put_contents($fullTargetPath, $css)) {
                throw new Exception("STY0005", "$fullTargetPath could not be written for file $destFile");
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
