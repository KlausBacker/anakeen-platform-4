<?php

namespace Anakeen\Pu\Config;

use Anakeen\Workflow\ImportWorkflowConfiguration;

class TestCaseWorkflowConfig extends TestCaseConfig
{
    /**
     * Import a file workflow description
     *
     * @param string|string[] $file file path
     *
     * @throws \Anakeen\Exception
     */
    protected static function importWorkflowConfiguration($file)
    {
        $realfile = $file;
        if (!file_exists($realfile)) {
            $realfile = static::$testDataDirectory . "/" . $file;
        }
        if (!file_exists($realfile)) {
            throw new \Anakeen\Exception(sprintf("File '%s' not found in '%s'.", $file, $realfile));
        }
        $oImport = new ImportWorkflowConfiguration();
        $oImport->importAll($realfile);
        $err = $oImport->getErrorMessage();
        if ($err) {
            throw new \Anakeen\Exception($err);
        }
    }
}
