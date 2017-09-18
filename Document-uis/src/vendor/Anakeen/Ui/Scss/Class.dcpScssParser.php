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

require_once 'vendor/Anakeen/Ui/PhpLib/vendor/leafo/scssphp/scss.inc.php';

class dcpScssParser implements ICssParser
{
    protected $_srcFile = null;
    protected $_styleConfig = array();
    protected $_options = array();
    protected $commandMode = false;
    /**
     * @param string $srcFile    path or array of path of source file(s) relative to server root
     * @param array           $options     array of options
     * @param array           $styleConfig full style configuration
     */
    public function __construct($srcFile, Array $options, Array $styleConfig)
    {
        if (is_array($srcFile)) {
            new \Exception("SCSS parser take only one file");
        }

        $this->_srcFile = $srcFile;

        if(`which sassc`) {
            echo "MODE LIBCSASS ACTIVATED\n";
            $this->commandMode = true;
        }

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

        $exception = null;
        try {
            if ($this->commandMode) {
                $this->commandGen($fullTargetPath);
            } else {
                $this->phpGen($fullTargetPath);
            }
        }
        catch(\Exception $e) {
            $exception = $e;
        }

        if ($exception !== null) {
            throw $exception;
        }
    }

    protected function phpGen($fullTargetPath) {
        $compiler = new Compiler();
        $compiler->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
        $server = new Server(DEFAULT_PUBDIR, null, $compiler);
        $srcFullPath = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . $this->_srcFile;
        $compiler->addImportPath(dirname($srcFullPath));
        $css = $server->checkedCachedCompile($srcFullPath, $fullTargetPath);
        if (false === file_put_contents($fullTargetPath, $css)) {
            throw new Exception("STY0005", "$fullTargetPath could not be written for file $fullTargetPath");
        }
    }

    protected function commandGen($fullTargetPath) {
        $srcFullPath = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . $this->_srcFile;
        shell_exec(sprintf('sassc -m %s %s', $srcFullPath, $fullTargetPath));
    }
}