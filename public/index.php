<?php

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->addPsr4('', realpath(__DIR__ . '/../src/'));

use \Slim\Http\Request;
use \Slim\Http\Response;

$rootPath = realpath(__DIR__ . "/../");
set_include_path(get_include_path() . PATH_SEPARATOR . $rootPath . DIRECTORY_SEPARATOR . 'include');
putenv('WIFF_ROOT=' . $rootPath);

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'debug' => true,
    ]
]);

/**
 * Main Html page to display current configuration
 */
$app->get('/', function (Request $request, Response $response, array $args) {
    return (new \Control\MainPage())($request, $response);
});

/**
 * Main Html page to display current configuration
 */
$app->get('/api/info', function (Request $request, Response $response, array $args) {
    return (new \Control\Api\Info())($request, $response);
});

$app->run();