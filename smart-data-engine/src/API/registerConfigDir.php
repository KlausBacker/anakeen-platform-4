<?php


$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Register new path for route and parameter config");
$path = $usage->addOptionalParameter("path", "The path to register", function ($path, $argName, \Anakeen\Script\ApiUsage $apiusage) {
    if ($path === \Anakeen\Script\ApiUsage::GET_USAGE) {
        return "";
    }
    $abspath=\Anakeen\Core\ContextManager::getRootDirectory()."/".$path;
    if (!is_dir($abspath)) {
        $apiusage->exitError(sprintf("Error: config directory \"%s\" not exists.", $path));
    }
    if (!is_readable($abspath)) {
        $apiusage->exitError(sprintf("Error:  config directory \"%s\" not readable.", $path));
    }
    return '';
});
$todo = $usage->addOptionalParameter("action", "Action to do", ["add", "delete", "list"], "add");

$usage->verify();

if ($todo !== "list" and !$path) {
    $usage->exitError("Path is needed except for \"list\" action");
}

switch ($todo) {
    case "list":
        $paths=\Anakeen\Router\RouterManager::getRouterConfigPaths();
        print "Config paths :\n\t";
        print(implode("\n\t", $paths)."\n.");
        break;

    case "add":
        \Anakeen\Router\RouterManager::addRouterConfigPath($path);
        break;
    case "delete":
        \Anakeen\Router\RouterManager::deleteRouterConfigPath($path);
        break;
}
