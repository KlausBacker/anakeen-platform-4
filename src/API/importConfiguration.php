<?php

/**
 * Import configuration for Smart Structure
 */

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Import configuration file");
$filename = $usage->addOptionalParameter("file", "the configuration file path (XML)");
$glob = $usage->addOptionalParameter("glob", "the configuration glob path");
$analyze = $usage->addOptionalParameter("analyze", "analyze only", array(
    "yes",
    "no"
), "no");

$logfile = $usage->addOptionalParameter("log", "log file output");
$verbose = $usage->addEmptyParameter("verbose", "Verbose mode");
$debug = $usage->addEmptyParameter("debug", "Debug mode");

$usage->verify();

if (!$filename && !$glob) {
    throw new \Anakeen\Script\Exception("filename or glob parameter needed");
}

if ($filename && $glob) {
    throw new \Anakeen\Script\Exception("use filename OR glob");
}

if ($glob) {
    $configFiles = \Anakeen\Core\Utils\Glob::glob($glob, 0, true);
} elseif (!is_file($filename)) {
    \Anakeen\Core\ContextManager::exitError(sprintf(___("import file '%s' is not a valid file", "sde"), $filename));
} else {
    $configFiles = [$filename];
}
if ($logfile) {
    if (file_exists($logfile) && (!is_writable($logfile))) {
        \Anakeen\Core\ContextManager::exitError(sprintf(___("log file \"%s\" not writable", "sde"), $logfile));
    }
    if (!file_exists($logfile)) {
        $f = @fopen($logfile, 'a');
        if ($f === false) {
            \Anakeen\Core\ContextManager::exitError(sprintf(_("log file \"%s\" not writable"), $logfile));
        }
        fclose($f);
    }
}


$hasWorkflow=class_exists(\Anakeen\Workflow\ImportWorkflowConfiguration::class);
$hasUi=class_exists(\Anakeen\Ui\ImportRenderConfiguration::class);

if ($hasUi) {
    if ($hasWorkflow) {
        $oImport = new \Anakeen\Workflow\ImportWorkflowConfiguration();
    } else {
        $oImport = new \Anakeen\Ui\ImportRenderConfiguration();
    }
} else {
    $oImport = new \Anakeen\Core\Internal\ImportSmartConfiguration();
}


$oImport->setOnlyAnalyze($analyze !== "no");
$oImport->setVerbose($debug);

$point = "IMPCFG";
\Anakeen\Core\DbManager::savePoint($point);


foreach ($configFiles as $configFile) {
    if ($verbose) {
        printf("Importing config \"%s\".\n", $configFile);
    }
    if ($hasUi) {
        $oImport->importData($configFile);
    }

    if ($oImport->getErrorMessage()) {
        break;
    }
    $oImport->import($configFile);

    if ($oImport->getErrorMessage()) {
        break;
    }

    if ($hasUi) {
        $oImport->importRender($configFile);
    }
    if ($oImport->getErrorMessage()) {
        break;
    }
}

$err = $oImport->getErrorMessage();
/*
if (!$err && $hasUi === true) {
    $oUiImport = new \Anakeen\Ui\ImportRenderConfiguration();
    $oUiImport->setOnlyAnalyze($analyze !== "no");
    $oUiImport->setVerbose($debug);
    foreach ($configFiles as $configFile) {
        if ($verbose) {
            printf("Importing Ui part \"%s\".\n", $configFile);
        }
        $oUiImport->importRender($configFile);

        if ($oUiImport->getErrorMessage()) {
            break;
        }
    }
    $err = $oUiImport->getErrorMessage();
}
*/
if ($err) {
    \Anakeen\Core\DbManager::rollbackPoint($point);
    \Anakeen\Core\ContextManager::exitError($err);
} else {
    \Anakeen\Core\DbManager::commitPoint($point);
}