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


$usage->verify();


$action=\Anakeen\Core\ContextManager::getCurrentAction();

if (!file_exists($filename)) {
    $action->ExitError(sprintf(_("import file %s not found"), $filename));
}
if (!is_file($filename)) {
    $action->exitError(sprintf(_("import file '%s' is not a valid file"), $filename));
}
if ($logfile) {
    if (file_exists($logfile) && (!is_writable($logfile))) {
        $action->ExitError(sprintf(_("log file %s not writable"), $logfile));
    }
    if (!file_exists($logfile)) {
        $f = @fopen($logfile, 'a');
        if ($f === false) {
            $action->ExitError(sprintf(_("log file %s not writable"), $logfile));
        }
        fclose($f);
    }
}

$oImport = new \Anakeen\Core\Internal\ImportSmartConfiguration();
$oImport->setOnlyAnalyze($analyze !== "no");
$oImport->import($filename);


$err = $oImport->getErrorMessage();
if ($err) {
    $action->ExitError($err);
}
