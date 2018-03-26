#!/usr/bin/env php
<?php
/**
 * Anakeen SHELL
 */

ini_set("max_execution_time", "0");
ini_set("memory_limit", -1);


$loader = require __DIR__ . '/vendor/Anakeen/lib/vendor/autoload.php';
require __DIR__ . '/vendor/Anakeen/WHAT/Lib.Prefix.php';

// To get global functions like ___
require __DIR__ . "/vendor/Anakeen/WHAT/Lib.Common.php";
// Need to load universal autoload also
require __DIR__ . "/vendor/Anakeen/WHAT/autoload.php";

$loader->addPsr4('Anakeen\\', __DIR__ . '/vendor/Anakeen/');

$programName=array_shift($argv);
Anakeen\Script\ShellManager::recordArgs($argv, $programName);

if (Anakeen\Script\ShellManager::getArg("help")) {
    print Anakeen\Script\ShellManager::getUsage();
    exit(0);
}

set_exception_handler(function ($e) {
    \Anakeen\Script\ShellManager::exceptionHandler($e);
});
register_shutdown_function(function () {
    \Anakeen\Script\ShellManager::shutdownHandler();
});


$apiScript = Anakeen\Script\ShellManager::getArg("script");
$routeReference = Anakeen\Script\ShellManager::getArg("route");
$isSystem = Anakeen\Script\ShellManager::getArg("system");

if (!(!empty($apiScript) xor !empty($routeReference) xor !empty($isSystem))) {
    if (empty($apiScript) && empty($routeReference) && empty($isSystem)) {
        print "Argument script, route or system must be used.\n";
    } else {
        print "Argument script, route or system cannot be used together.\n";
    }
    print Anakeen\Script\ShellManager::getUsage();
    exit(1);
}

if ($apiScript) {
    if (Anakeen\Script\ShellManager::getArg("list") && $apiScript === true) {
        print "Script List :\n\t- ";
        print  implode("\n\t- ", Anakeen\Script\ShellManager::getScripts());
        print "\n";
    } else {
        if ($apiScript === true) {
            print Anakeen\Script\ShellManager::getUsage();
            exit(1);
        }
        Anakeen\Script\ShellManager::runScript($apiScript);
    }
}

if ($routeReference) {
    $routeCmd = new \Anakeen\Script\RouteCommand();
    if (Anakeen\Script\ShellManager::getArg("list") & $routeReference === true) {
        print "Route List :\n ";
        $routeList = $routeCmd->getRouteList();
        ksort($routeList);
        foreach ($routeList as $idRoute => $routerInfo) {
            if (is_array($routerInfo->pattern)) {
                $routerInfo->pattern = current($routerInfo->pattern);
            }
            if (preg_match_all("/\{([^\}:]*)[\}:]/", $routerInfo->pattern, $regs)) {
                //print_r([$routerInfo->pattern, $regs]);
                $args = sprintf("(%s)", implode(", ", $regs[1]));
            } else {
                $args = "()";
            }
            printf("\t- %40s :\t [%s] %s\n", $idRoute . $args, implode(", ", $routerInfo->methods), $routerInfo->description);
        }
    } else {
        $output = $routeCmd->requestRoute($routeReference, Anakeen\Script\ShellManager::getArgs());
        print $output;
    }
}

if ($isSystem) {
    $system = new \Anakeen\Script\System();
    if (Anakeen\Script\ShellManager::getArg("verbose")) {
        $system->setVerbose(true);
    }
    if (Anakeen\Script\ShellManager::getArg("start")) {
        $system->start();
    } elseif (Anakeen\Script\ShellManager::getArg("stop")) {
        $system->stop();
    } elseif (Anakeen\Script\ShellManager::getArg("unstop")) {
        $system->unstop();
    } elseif (Anakeen\Script\ShellManager::getArg("resetRouteConfig")) {
        $system->resetRouteConfig();
    } elseif (Anakeen\Script\ShellManager::getArg("upgradeVersion")) {
        $system->refreshJsVersion();
    } elseif (Anakeen\Script\ShellManager::getArg("clearFile")) {
        $system->clearFileCache();
    } elseif (Anakeen\Script\ShellManager::getArg("style")) {
        $system->style();
    } elseif (Anakeen\Script\ShellManager::getArg("resetAutoloader")) {
        $system->clearAutoloadCache();
    } else {
        print Anakeen\Script\ShellManager::getUsage();
        exit(1);
    }
}
