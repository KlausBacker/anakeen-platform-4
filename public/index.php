<?php

$loader=require __DIR__."/../vendor/autoload.php";
$loader->addPsr4('', realpath(__DIR__. '/../src/'));

$rootPath=realpath(__DIR__."/../");
set_include_path(get_include_path() . PATH_SEPARATOR . $rootPath . DIRECTORY_SEPARATOR . 'include');
putenv('WIFF_ROOT=' .$rootPath );

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'debug' => true,
    ]]);
$app->get('/', function (\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
    return (new \Control\MainPage())($request, $response);
});
$app->run();