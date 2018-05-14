<?php
namespace Anakeen\Routes\Admin;

class Plugins
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $testPath =__DIR__."/../../AdminCenter/Layout/pluginsListTest.json";
        $fileTest = file_get_contents($testPath);
        return $response->withJson(json_decode($fileTest, true));
    }
}