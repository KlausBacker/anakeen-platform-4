<?php

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Import search domain definition");
$filename = $usage->addRequiredParameter(
    "file",
    "the input XML file",
    function ($values, $argName, \Anakeen\Script\ApiUsage $apiusage) {
        if ($values === \Anakeen\Script\ApiUsage::GET_USAGE) {
            return "";
        }
        if (is_file($values) && !is_readable($values)) {
            $apiusage->exitError(sprintf("Error: file output \"%s\" not readable.", $values));
        }
        return '';
    }
);
$reindex = $usage->addEmptyParameter("reindex", "Send then complete reindexation of search data");
$verbose = $usage->addEmptyParameter("verbose", "verbose mode");

$usage->verify();

$import = new \Anakeen\Fullsearch\ImportSearchConfiguration($filename);
$domain = $import->recordConfig();

if ($verbose) {
    print json_encode($domain, JSON_PRETTY_PRINT);
}

if ($reindex) {
    $domain->updateIndexSearchData(function (\Anakeen\SmartElement $se) {
        printf("%s\n", $se->getTitle());
    });
}
