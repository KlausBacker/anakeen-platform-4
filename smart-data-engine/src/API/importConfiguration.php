<?php

/**
 * Import configuration for Smart Structure
 * Import Accounts
 * Import Smart Element Data
 */

use Anakeen\Core\Internal\ImportAnyConfiguration;

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Import configuration file");
$filename = $usage->addOptionalParameter("file", "the configuration file path (XML)");
$glob = $usage->addOptionalParameter("glob", "the configuration glob path");
$analyze = $usage->addHiddenParameter("analyze", "analyze only - keep for compatibility");

$dry = $usage->addEmptyParameter("dry-run", "Analyse file only - no import is proceed");


$logfile = $usage->addOptionalParameter("log", "log file output");
$verbose = $usage->addEmptyParameter("verbose", "Verbose mode");
$debug = $usage->addEmptyParameter("debug", "Debug mode");

$usage->verify();

$dryRun = ($analyze === "yes") || $dry;

if (!$filename && !$glob) {
    throw new \Anakeen\Script\Exception("filename or glob parameter needed");
}

if ($filename && $glob) {
    throw new \Anakeen\Script\Exception("use filename OR glob");
}

if ($glob) {
    $configFiles = \Anakeen\Core\Utils\Glob::glob($glob, 0, true);
} elseif (!is_file($filename)) {
    \Anakeen\Core\ContextManager::exitError(sprintf(___("Import file '%s' is not found", "sde"), $filename));
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

$point = "IMPCFG";
\Anakeen\Core\DbManager::savePoint($point);

// -----------
// Pre Testing
$xmlErrors = [];
foreach ($configFiles as $configFile) {
    $err = ImportAnyConfiguration::checkValidity($configFile);
    if ($err) {
        $xmlErrors[] = $err;
    }
}

if ($xmlErrors) {
    throw new \Anakeen\Script\Exception(implode("\n", $xmlErrors));
}

$importObject = new ImportAnyConfiguration();
$importObject->setDryRun($dryRun);
$importObject->setVerbose($verbose);

// -------------------------------
// Process configuration files
foreach ($configFiles as $configFile) {
    if ($verbose) {
        printf("Parse file \"%s\".\n", $configFile);
    }

    $importObject->load($configFile);
    if ($verbose) {
        switch ($importObject->getImportType()) {
            case ImportAnyConfiguration::SMARTCONFIG:
                printf("\tImporting smart configuration from \"%s\".\n", $configFile);
                break;
            case ImportAnyConfiguration::ACCOUNTCONFIG:
                printf("\tImporting accounts configuration from \"%s\".\n", $configFile);
                break;
            case ImportAnyConfiguration::SMARTELEMENTCONFIG:
                printf("\tImporting Smart Elements data from \"%s\".\n", $configFile);
                break;
        }
    }

    try {
        $importObject->import();
    } catch (\Anakeen\Exception $exception) {
        if ($debug) {
            $data = $importObject->getDebugData();
            print(json_encode($data, JSON_PRETTY_PRINT));
            print "\n";
        }
        throw new \Anakeen\Script\Exception($exception->getMessage());
    }

    if ($verbose) {
        $data = $importObject->getVerboseMessages();
        print implode("\n", $data);
        print "\n";
    }
    if ($debug) {
        $data = $importObject->getDebugData();
        print(json_encode($data, JSON_PRETTY_PRINT));
        print "\n";
    }
}


if ($err) {
    \Anakeen\Core\DbManager::rollbackPoint($point);
    \Anakeen\Core\ContextManager::exitError($err);
} else {
    \Anakeen\Core\DbManager::commitPoint($point);
}
