<?php
/**
 * Record routes and global acls
 */

$usage = new ApiUsage();
$usage->setDefinitionText("Record routes from config directory");


$usage->verify();


$routeConfig = \Anakeen\Router\RouterLib::getRouterConfig();
$routeConfig->recordAccesses();

$routesConfig=new \Anakeen\Router\RoutesConfig();
$routesConfig->resetCache();
