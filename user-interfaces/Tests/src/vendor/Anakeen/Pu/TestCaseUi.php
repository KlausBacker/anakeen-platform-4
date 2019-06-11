<?php

namespace Anakeen\Pu;

use Anakeen\Pu\Config\TestCaseConfig;
use Anakeen\Ui\ImportRenderConfiguration;

class TestCaseUi extends TestCaseConfig
{
    protected static function importConfiguration($file)
    {
        $realfile = $file;
        if (!file_exists($realfile)) {
            $realfile = static::$testDataDirectory . "/" . $file;
        }
        if (!file_exists($realfile)) {
            throw new \Anakeen\Exception(sprintf("File '%s' not found in '%s'.", $file, $realfile));
        }
        $oImport = new ImportRenderConfiguration();
        $oImport->importAll($realfile);
        $err = $oImport->getErrorMessage();
        if ($err) {
            throw new \Anakeen\Exception($err);
        }
    }
}
