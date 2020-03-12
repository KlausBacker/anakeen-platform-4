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
$fromglob = $usage->addHiddenParameter("fromglob", "sub process of glob");

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
    if (count($configFiles) === 0) {
        print "No files detected in glob \"$glob\"\n";
    }
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
$err = "";
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
if (count($configFiles) === 1) {
    $configFile = $configFiles[0];
    if ($verbose) {
        if (!$fromglob) {
            printf("%s> Parse file \"%s\".\n", date("Y-m-d H:i:s"), $configFile);
        }
        $mb1 = microtime(true);
    }

    \Anakeen\Core\DbManager::savePoint($point);

    $importObject->clearVerboseMessages();
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
        \Anakeen\Core\DbManager::commitPoint($point);
    } catch (\Anakeen\Exception $exception) {
        \Anakeen\Core\DbManager::rollbackPoint($point);
        if ($debug) {
            $data = $importObject->getDebugData();
            print(json_encode($data, JSON_PRETTY_PRINT));
            print "\n";
        }
        throw new \Anakeen\Script\Exception($exception->getMessage());
    }

    if ($verbose) {
        $data = $importObject->getVerboseMessages();
        print "\n\t";
        print implode("\n\t", str_replace("\n", "\n\t", $data));
        print "\n";
        printf("%s> Elapsed time %.02fs.\n", date("Y-m-d H:i:s"), microtime(true) - $mb1);
    }
    if ($debug) {
        $data = $importObject->getDebugData();
        print(json_encode($data, JSON_PRETTY_PRINT));
        print "\n";
    }
} else {
    $opt = "--fromglob";
    if ($dryRun) {
        $opt .= " --dry-run";
    }
    if ($verbose) {
        $opt .= " --verbose";
    }
    if ($debug) {
        $opt .= " --debug";
    }
    foreach ($configFiles as $configFile) {
        if ($verbose) {
            printf("\033[32m%s> Parse file \"%s\".\033[0m\n", date("Y-m-d H:i:s"), $configFile);
            $mb1 = microtime(true);
        }
        $ankCmd = sprintf(
            "%s --script=importConfiguration --file=%s %s 2>&1",
            \Anakeen\Script\ShellManager::getAnkCmd(),
            escapeshellarg($configFile),
            $opt
        );
        $output = [];
        exec($ankCmd, $output, $retval);
        if ($retval === 0) {
            if ($verbose) {
                print(implode("\n\t", $output) . "\n");
            }
        } else {
            throw new \Anakeen\Script\Exception(sprintf(
                "Error importing \"%s\":\n%s",
                $configFile,
                implode("\n", $output)
            ));
        }
    }
}
