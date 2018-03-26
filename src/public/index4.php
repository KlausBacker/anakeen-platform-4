<?php

$loader = require __DIR__ . '/../vendor/Anakeen/lib/vendor/autoload.php';
require __DIR__ . "/../vendor/Anakeen/WHAT/Lib.Prefix.php";
require __DIR__ . "/../vendor/Anakeen/WHAT/Lib.Main.php";
//require __DIR__ . "/../vendor/Anakeen/WHAT/autoload.php";

$loader->addPsr4('Anakeen\\', __DIR__ . '/vendor/Anakeen/');
register_shutdown_function('handleFatalShutdown');
set_exception_handler('handleActionException');

if (ActionRouter::inMaintenance()) {
    $e = new \Anakeen\Router\Exception("Maintenance");
    $e->setUserMessage("The system is currently unavailable due to maintenance works");
    $e->setHttpStatus(503);
    throw $e;
}

$routeConfig = new \Anakeen\Router\RoutesConfig();
$routes = $routeConfig->getRoutes();
$middleWares = $routeConfig->getMiddlewares();

$app = \Anakeen\Router\RouterManager::getSlimApp();
// Add routes configuration to router
\Anakeen\Router\RouterManager::addRoutes($routes);
// Add middlewares configuration to router
\Anakeen\Router\RouterManager::addMiddlewares($middleWares);

// Run app
$app->run();
