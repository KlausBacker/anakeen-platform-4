<?php

namespace Anakeen\Routes\Admin;


use Anakeen\Core\ContextManager;

class MainPage
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page=__DIR__."/../../AdminCenter/Layout/adminCenterMainPage.html";

        if ($request->getQueryParam("debug") === "true") {
            $page=__DIR__."/../../AdminCenter/Layout/adminCenterMainPage-debug.html";
        }

        $template = file_get_contents($page);

        $version = \Dcp\Ui\UIGetAssetPath::getWs();

        $data = [
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCssBootstrap()
                ],
                [
                    "key" => "kendo",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCssKendo()
                ],
                [
                    "key" => "components",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCssSmartWebComponents()
                ],
                [
                    "key" => "admin",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCss("admin")
                ],
            ]
        ];

        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }

}