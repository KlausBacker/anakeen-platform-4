<?php

/**
 * Import configuration for Smart Structure
 */

use Anakeen\Core\SmartStructure\ExportConfiguration;
use Anakeen\Core\Utils\Xml;
use Anakeen\Exchange\ExportAccounts;

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


$hasWorkflow = class_exists(\Anakeen\Workflow\ImportWorkflowConfiguration::class);
$hasUi = class_exists(\Anakeen\Ui\ImportRenderConfiguration::class);

if ($hasUi) {
    if ($hasWorkflow) {
        $oImport = new \Anakeen\Workflow\ImportWorkflowConfiguration();
    } else {
        $oImport = new \Anakeen\Ui\ImportRenderConfiguration();
    }
} else {
    $oImport = new \Anakeen\Core\Internal\ImportSmartConfiguration();
}


$oImport->setOnlyAnalyze($dryRun);
$oImport->setVerbose($debug);

$point = "IMPCFG";
\Anakeen\Core\DbManager::savePoint($point);

// -----------
// Pre Testing
$xmlErrors = [];
foreach ($configFiles as $configFile) {
    $dom = new \DOMDocument();
    if (!@$dom->load($configFile)) {
        $xmlErrors[] = sprintf('Configuration file "%s" is not an xml file', $configFile);
    }

    if (!Xml::getPrefix($dom, ExportConfiguration::NSURL) &&
        !Xml::getPrefix(
            $dom,
            ExportAccounts::NSURI
        ) &&
        ($dom->documentElement->tagName !== "documents")) {
        $xmlErrors[] = sprintf('File "%s" is not detected has a configuration file', $configFile);
    }
}

if ($xmlErrors) {
    throw new \Anakeen\Script\Exception(implode("\n", $xmlErrors));
}

// -------------------------------
// Process configuration files
foreach ($configFiles as $configFile) {
    if ($verbose) {
        printf("Parse file \"%s\".\n", $configFile);
    }

    $dom = new \DOMDocument();
    $dom->load($configFile);
    if (!@$dom->load($configFile)) {
        throw new \Anakeen\Script\Exception(sprintf('Configuration file "%s" is not an xml file', $configFile));
    }

    if (Xml::getPrefix($dom, ExportConfiguration::NSURL)) {
        /** ============================================================
         * IMPORT Smart XML like routes, profile, structure, render config
         * ============================================================= */
        if ($verbose) {
            printf("\tImporting smart configuration from \"%s\".\n", $configFile);
        }
        $oImport->importAll($configFile);

        if ($oImport->getErrorMessage()) {
            break;
        }
        if ($debug) {
            print(json_encode($oImport->getVerboseMessages(), JSON_PRETTY_PRINT));
        }
    } elseif (Xml::getPrefix($dom, ExportAccounts::NSURI)) {
        /** ============================================================
         * IMPORT Account : user , role and group
         * ============================================================= */
        if ($verbose) {
            printf("\tImporting accounts configuration from \"%s\".\n", $configFile);
        }

        $import = new \Anakeen\Exchange\ImportAccounts();
        $import->setFile($configFile);
        $import->setAnalyzeOnly($dryRun);
        $import->import();

        if ($import->hasErrors()) {
            throw new \Anakeen\Script\Exception(implode("\n", $import->getErrors()));
        }
        if ($debug) {
            $report = $import->getReport();
            print(json_encode($report, JSON_PRETTY_PRINT));
        }
    } elseif ($dom->documentElement->tagName === "documents") {
        /** ============================================================
         * IMPORT Smart Element Data
         * ============================================================= */
        if ($verbose) {
            printf("\tImporting Smart Elements data from \"%s\".\n", $configFile);
        }

        $iXml = new \Anakeen\Exchange\ImportXml();
        $iXml->analyzeOnly($dryRun);
        $cr = $iXml->importSingleXmlFile($configFile);

        if ($debug) {
            print(json_encode($cr, JSON_PRETTY_PRINT));
        }
    }
}

$err = $oImport->getErrorMessage();

if ($err) {
    \Anakeen\Core\DbManager::rollbackPoint($point);
    \Anakeen\Core\ContextManager::exitError($err);
} else {
    \Anakeen\Core\DbManager::commitPoint($point);
}
