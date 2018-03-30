<?php


namespace Dcp\Style;

interface IParser
{
    /**
     * @param string|string[] $srcFiles    path or array of path of source file(s) relative to server root
     * @param array           $options
     * @param array           $styleConfig full style configuration
     */
    public function __construct($srcFiles, array $options, array $styleConfig);

    /**
     * @param string $destFile destination file path relative to server root (if null, parsed result is returned)
     *
     * @throws Exception
     * @return mixed
     */
    public function gen($destFile = null);
}

interface ICssParser extends IParser
{
}


