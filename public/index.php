<?php

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->addPsr4('', realpath(__DIR__ . '/../src/'));

use \Slim\Http\Request;
use \Slim\Http\Response;

$rootPath = realpath(__DIR__ . "/../");
set_include_path(get_include_path() . PATH_SEPARATOR . $rootPath . DIRECTORY_SEPARATOR . 'include');
putenv('WIFF_ROOT=' . $rootPath);


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
$app->get('/api/search/', function (Request $request, Response $response) {
    return (new \Control\Api\Search())($request, $response);
});
$app->get('/api/modules/', function (Request $request, Response $response) {
    return (new \Control\Api\Show())($request, $response);
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