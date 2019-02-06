<?php

require __DIR__ . '/../vendor/Anakeen/autoload.php';
require __DIR__ . "/../vendor/Anakeen/WHAT/Lib.Prefix.php";


// TODO To delete when legacy functions will have disappeared
require __DIR__ . "/../vendor/Anakeen/Legacy/LegacyDocManager.php";

register_shutdown_function(function () {
    \Anakeen\Router\FatalHandler::handleFatalShutdown();
});
set_exception_handler(function ($e) {
    \Anakeen\Router\FatalHandler::handleActionException($e);
});

if (\Anakeen\Core\ContextManager::inMaintenance()) {
    $e = new \Anakeen\Router\Exception("Maintenance");
    $e->setUserMessage("The system is currently unavailable due to maintenance works");
    $e->setHttpStatus(503);
    throw $e;
}

$routes = \Anakeen\Router\RouterManager::getRoutes();
$middleWares = \Anakeen\Router\RouterManager::getMiddlewares();

$app = \Anakeen\Router\RouterManager::getSlimApp();
// Add routes configuration to router
\Anakeen\Router\RouterManager::addRoutes($routes);
// Add middlewares configuration to router
\Anakeen\Router\RouterManager::addMiddlewares($middleWares);

// Run app
$app->run();
