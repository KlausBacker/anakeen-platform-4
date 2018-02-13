<?php

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require __DIR__ . '/../vendor/Anakeen/lib/vendor/autoload.php';
require_once __DIR__ . "/../vendor/Anakeen/WHAT/Lib.Prefix.php";
require_once __DIR__ . "/../vendor/Anakeen/WHAT/Lib.Main.php";
require_once('WHAT/autoload.php');

register_shutdown_function('handleFatalShutdown');
set_exception_handler('handleActionException');

// To add other path
// @TODO inspect config autoload path
$loader->addPsr4('Dcp\\', __DIR__ . '/../vendor/Anakeen/');


$routeConfig = \Anakeen\Router\RouterLib::getRouterConfig();
$routes = $routeConfig->getRoutes();
$middleWares = $routeConfig->getMiddlewares();

$app=\Anakeen\Router\RouterManager::getSlimApp();
\Anakeen\Router\RouterManager::addRoutes($routes);
// Add middlewares to the application
\Anakeen\Router\RouterManager::addMiddlewares($middleWares);


// Define app routes
$app->get('/', function ($request, $response, $args) {
    /**
     * @var \Slim\Http\response $response
     */
    return $response->write("<h1>Welcome to Anakeen Platform 4.</h1>");
});

// Run app
$app->run();
