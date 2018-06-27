<?php


namespace Dcp\Style;




class dcpCssConcatParser implements ICssParser
{
    protected $_srcFiles = null;

    /**
     * @param string|string[] $srcFiles    path or array of path of source file(s) relative to server root
     * @param array           $options
     * @param array           $styleConfig full style configuration
     */
    public function __construct($srcFiles, array $options, array $styleConfig)
    {
        if (is_array($srcFiles)) {
            $this->_srcFiles = $srcFiles;
        } else {
            $this->_srcFiles = array(
                $srcFiles
            );
        }
    }

    /**
     * @param string $destFile destination file path relative to server root (if null, parsed result is returned)
     *
     * @throws Exception
     */
    public function gen($destFile = null)
    {
        $pubDir = DEFAULT_PUBDIR;
        // prepare target dir
        $fullTargetPath = $pubDir . DIRECTORY_SEPARATOR . $destFile;
        $fullTargetDirname = dirname($fullTargetPath);
        if (!is_dir($fullTargetDirname) && (false === mkdir($fullTargetDirname, 0777, true))) {
            throw new Exception("STY0005", "$fullTargetDirname dir could not be created for file $destFile");
        }

        $targetHandler = fopen($fullTargetPath, 'w');
        if (false === $targetHandler) {
            throw new Exception("STY0005", "$destFile dir could not be created");
        }

        foreach ($this->_srcFiles as $srcPath) {
            $srcFullPath = $pubDir . DIRECTORY_SEPARATOR . $srcPath;
            if (!is_readable($srcFullPath)) {
                if (!file_exists($srcFullPath)) {
                    $msg = "source file $srcFullPath does not exists for file $destFile";
                } else {
                    $msg = "source file $srcFullPath is not readable for file $destFile";
                }
                throw new Exception("STY0004", $msg);
            }
            if (false === fwrite($targetHandler, file_get_contents($srcFullPath))) {
                throw new Exception("STY0005", "data from $srcFullPath could not be written to file $fullTargetPath");
            }
        }
        if (false === fclose($targetHandler)) {
            throw new Exception("STY0005", "$fullTargetPath could not be closed");
        }
    }
}

class dcpCssTemplateParser implements ICssParser
{
    protected $_srcFiles = null;
    protected $_styleConfig = array();

    /**
     * @param string|string[] $srcFiles    path or array of path of source file(s) relative to server root
     * @param array           $options     array of options
     * @param array           $styleConfig full style configuration
     */
    public function __construct($srcFiles, array $options, array $styleConfig)
    {
        if (is_array($srcFiles)) {
            $this->_srcFiles = $srcFiles;
        } else {
            $this->_srcFiles = array(
                $srcFiles
            );
        }
        $this->_styleConfig = $styleConfig;
    }

    /**
     * @param string $destFile destination file path relative to server root (if null, parsed result is returned)
     *
     * @throws Exception
     * @return void
     */
    public function gen($destFile = null)
    {
        $template = '';
        $pubDir = DEFAULT_PUBDIR;
        global $action;
        foreach ($this->_srcFiles as $srcPath) {
            $srcFullPath = $pubDir . DIRECTORY_SEPARATOR . $srcPath;
            if (!is_readable($srcFullPath)) {
                if (!file_exists($srcFullPath)) {
                    $msg = "source file $srcFullPath does not exists for file $destFile";
                } else {
                    $msg = "source file $srcFullPath is not readable for file $destFile";
                }
                throw new Exception("STY0004", $msg);
            }
            $template .= file_get_contents($srcFullPath);
        }
        // prepare target dir
        $fullTargetPath = $pubDir . DIRECTORY_SEPARATOR . $destFile;
        $fullTargetDirname = dirname($fullTargetPath);
        if (!is_dir($fullTargetDirname) && (false === mkdir($fullTargetDirname, 0777, true))) {
            throw new Exception("STY0005", "$fullTargetDirname dir could not be created for file $destFile");
        }

        $lay = new \Layout("", $action, $template);
        $template = $lay->gen();
        $keyForStyle = preg_replace("/css\//", "", $destFile);
        if (!isset($this->_styleConfig["sty_rules"]["css"][$keyForStyle]["flags"])
            || $this->_styleConfig["sty_rules"]["css"][$keyForStyle]["flags"] !== \Anakeen\Core\Internal\Style::RULE_FLAG_PARSE_ON_RUNTIME) {
            $subRepositoryLevel = substr_count($destFile, "/");
            $levelToGo = "";
            for ($i = 0; $i < $subRepositoryLevel; $i++) {
                $levelToGo .= "../";
            }
            $template = preg_replace('/(url\()\s*([\'"]?)\s*(.*?)\s*(\2\s*\))/', "$1$2" . $levelToGo . "$3$4", $template);
        }
        if (false === file_put_contents($fullTargetPath, $template)) {
            throw new Exception("STY0005", "$fullTargetPath could not be written for file $destFile");
        }
    }
}

class dcpCssCopyDirectory implements ICssParser
{
    protected $_srcFiles = null;

    /**
     * @param string|string[] $srcFiles    path or array of path of source file(s) relative to server root
     * @param array           $options     array of options
     * @param array           $styleConfig full style configuration
     */
    public function __construct($srcFiles, array $options, array $styleConfig)
    {
        if (is_array($srcFiles)) {
            $this->_srcFiles = $srcFiles;
        } else {
            $this->_srcFiles = array(
                $srcFiles
            );
        }
    }

    /**
     * @param string $destFile destination file path relative to server root (if null, parsed result is returned)
     *
     * @throws Exception
     * @return void
     */
    public function gen($destFile = null)
    {
        $pubDir = DEFAULT_PUBDIR;

        foreach ($this->_srcFiles as $srcPath) {
            $srcFullPath = $pubDir . DIRECTORY_SEPARATOR . $srcPath;
            if (!is_dir($srcFullPath)) {
                throw new Exception("STY0009", $srcFullPath);
            }
            if (!is_dir($pubDir . DIRECTORY_SEPARATOR . $destFile)) {
                $r = mkdir($pubDir . DIRECTORY_SEPARATOR . $destFile);
                if ($r === false) {
                    throw new Exception("STY0008", $pubDir . DIRECTORY_SEPARATOR . $destFile);
                }
            }
            $cpCmd = sprintf("cp -r %s/* %s", escapeshellarg($srcFullPath), escapeshellarg($pubDir . DIRECTORY_SEPARATOR . $destFile));
            $r = shell_exec("$cpCmd 2>&1 && echo 1");
            if ($r === null) {
                throw new Exception("STY0010", $srcFullPath, $pubDir . DIRECTORY_SEPARATOR . $destFile);
            }
        }
    }
}

