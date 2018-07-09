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
//            "JS" => [
//                ["key" =>"jquery",
//                "path" => \Dcp\Ui\UIGetAssetPath::getJSJqueryPath()],
//                [
//                    "key" =>"kendo",
//                    "path" => \Dcp\Ui\UIGetAssetPath::getJSKendoPath()
//                ],
//                ["key" =>"ank-components",
//                    "path" => \Dcp\Ui\UIGetAssetPath::getSmartWebComponentsPath()]
//            ],
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => "/css/ank/document/bootstrap.css?ws=" . $version
                ],
                [
                    "key" => "kendo",
                    "path" => "/css/ank/document/kendo.css?ws=" . $version
                ],
                [
                    "key" => "admin",
                    "path" => '/css/ank/admin-center/admin-center.css?ws='.$version
                ]
            ]
        ];

        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }

}