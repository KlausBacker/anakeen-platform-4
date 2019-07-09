<?php

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->addPsr4('', realpath(__DIR__ . '/../src/'));

use \Slim\Http\Request;
use \Slim\Http\Response;

$rootPath = realpath(__DIR__ . "/../");
set_include_path(get_include_path() . PATH_SEPARATOR . $rootPath . DIRECTORY_SEPARATOR . 'include');
putenv('WIFF_ROOT=' . $rootPath);

register_shutdown_function(function () {
    Control\Internal\FatalHandler::handleFatalShutdown();
});

set_exception_handler(function (\Exception $e) {
    header("Content-Type: application/json");
    print (json_encode([
        "exception" => $e->getMessage()
    ]));

});
$app = new \Slim\App([
    "errorHandler" => function () {
        return new \Control\Api\ErrorHandler();
    },
    'settings' => [
        'displayErrorDetails' => true,
        'debug' => true,
    ]
]);
/**
 * Main Html page to display current configuration
 */
$app->get('/', function (Request $request, Response $response) {
    return (new \Control\MainPage())($request, $response);
});

/**
 * REST Api Configuration
 */
$app->get('/api/info', function (Request $request, Response $response) {
    return (new \Control\Api\Info())($request, $response);
});
$app->get('/api/status', function (Request $request, Response $response) {
    return (new \Control\Api\Status())($request, $response);
});
$app->get('/api/parameters/internal/', function (Request $request, Response $response) {
    return (new \Control\Api\GetInternalParameters())($request, $response);
});
$app->put('/api/parameters/internal/{name}', function (Request $request, Response $response, array $args) {
    return (new \Control\Api\SetInternalParameter())($request, $response, $args);
});
$app->get('/api/parameters/modules/', function (Request $request, Response $response) {
    return (new \Control\Api\GetModuleParameters())($request, $response);
});
$app->get('/api/search/', function (Request $request, Response $response) {
    return (new \Control\Api\Search())($request, $response);
});
$app->get('/api/modules/', function (Request $request, Response $response) {
    return (new \Control\Api\Show())($request, $response);
});
$app->get('/api/registeries/', function (Request $request, Response $response) {
    return (new \Control\Api\Registeries())($request, $response);
});
$app->post('/api/registeries/{name}', function (Request $request, Response $response, array $args) {
    return (new \Control\Api\AddRegistry())($request, $response, $args);
});
$app->delete('/api/registeries/{name}', function (Request $request, Response $response, array $args) {
    return (new \Control\Api\DeleteRegistry())($request, $response, $args);
});

$app->get('/api/modules/{name}', function (Request $request, Response $response, array $args) {
    return (new \Control\Api\ShowModule())($request, $response, $args);
});
$app->post('/api/modules/{name}', function (Request $request, Response $response, array $args) {
    return (new \Control\Api\InstallModule())($request, $response, $args);
});
$app->post('/api/modules', function (Request $request, Response $response) {
    return (new \Control\Api\InstallAppFile())($request, $response);
});
$app->post('/api/modules/', function (Request $request, Response $response) {
    return (new \Control\Api\Install())($request, $response);
});

$app->put('/api/modules/', function (Request $request, Response $response) {
    return (new \Control\Api\Update())($request, $response);
});
$app->put('/api/modules/{name}', function (Request $request, Response $response, array $args) {
    return (new \Control\Api\UpdateModule())($request, $response, $args);
});

$app->run();