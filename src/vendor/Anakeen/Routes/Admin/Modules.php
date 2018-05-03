<?php
namespace Anakeen\Routes\Admin;

class Modules
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $testPath =__DIR__."/../../AdminCenter/Layout/modulesListTest.json";
        $fileTest = file_get_contents($testPath);
        return $response->withJson(json_decode($fileTest, true));
    }
}