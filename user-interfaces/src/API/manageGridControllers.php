<?php

use Anakeen\Components\Grid\SmartGridControllerParameter;

$usage = new \Anakeen\Script\ApiUsage();

$usage->setDefinitionText("Register Smart Element Grid controller configuration");
$cmd = $usage->addRequiredParameter("cmd", "command to execute", array(
    "list",
    "register",
    "unregister",
    "unregister-all"
));
$filepath = $usage->addOptionalParameter("file", "the configuration file path (XML)");
$usage->verify();

switch ($cmd) {
    case "list":
        SmartGridControllerParameter::listAllConfig();
        break;
    case "register":
        SmartGridControllerParameter::addConfiguration($filepath);
        break;
    case "unregister":
        SmartGridControllerParameter::deleteConfig($filepath);
        break;
    case "unregister-all":
        SmartGridControllerParameter::deleteAllConfig();
        break;
}