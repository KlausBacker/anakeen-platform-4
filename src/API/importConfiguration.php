<?php

/**
 * Import configuration for Smart Structure
 */

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Import configuration file");
$filename = $usage->addRequiredParameter("file", "the configuration file path (XML)");
$analyze = $usage->addOptionalParameter("analyze", "analyze only", array(
    "yes",
    "no"
), "no");

$logfile = $usage->addOptionalParameter("log", "log file output");
$verbose = $usage->addEmptyParameter("verbose", "Verbose mode");

$usage->verify();


if (!file_exists($filename)) {
    \Anakeen\Core\ContextManager::exitError(sprintf(_("import file %s not found"), $filename));
}
if (!is_file($filename)) {
    \Anakeen\Core\ContextManager::exitError(sprintf(_("import file '%s' is not a valid file"), $filename));
}
if ($logfile) {
    if (file_exists($logfile) && (!is_writable($logfile))) {
        \Anakeen\Core\ContextManager::exitError(sprintf(_("log file %s not writable"), $logfile));
    }
    if (!file_exists($logfile)) {
        $f = @fopen($logfile, 'a');
        if ($f === false) {
            \Anakeen\Core\ContextManager::exitError(sprintf(_("log file %s not writable"), $logfile));
        }
        fclose($f);
    }
}

$oImport = new \Anakeen\Core\Internal\ImportSmartConfiguration();
$oImport->setOnlyAnalyze($analyze !== "no");
$oImport->setVerbose($verbose);

$point = "IMPCFG";
\Anakeen\Core\DbManager::savePoint($point);
$oImport->import($filename);

$err = $oImport->getErrorMessage();

if (! $err && class_exists(\Anakeen\Ui\ImportRenderConfiguration::class)) {
    $oUiImport = new \Anakeen\Ui\ImportRenderConfiguration();
    $oUiImport->setOnlyAnalyze($analyze !== "no");
    $oUiImport->setVerbose($verbose);
    $oUiImport->import($filename);
    $err = $oUiImport->getErrorMessage();
}

if ($err) {
    \Anakeen\Core\DbManager::rollbackPoint($point);
    \Anakeen\Core\ContextManager::exitError($err);
} else {
    \Anakeen\Core\DbManager::commitPoint($point);
}
