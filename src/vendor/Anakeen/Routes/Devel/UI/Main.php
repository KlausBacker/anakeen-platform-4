<?php

namespace Anakeen\Routes\Devel\UI;

use Dcp\Ui\UIGetAssetPath;

class Main
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/Layout/main.html.mustache";
        $mustache = new \Mustache_Engine();
        $data = [
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => UIGetAssetPath::getCssBootstrap()
                ],
                [
                    "key" => "kendo",
                    "path" => UIGetAssetPath::getCssKendo()
                ],
                [
                    "key" => "components",
                    "path" => UIGetAssetPath::getCssSmartWebComponents()
                ],
            ],
            "JS_DEPS" => [
                [
                    "key" =>"jquery",
                    "path" => UIGetAssetPath::getJSJqueryPath()
                ],
                [
                    "key" =>"kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ]
            ],
            "JS" => [
                [
                    "key" => "admin",
                    "path" => UIGetAssetPath::getElementAssets(
                        "developmentCenter",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["main"]["js"]
                ]
            ]
        ];
        $template = file_get_contents($page);
        return $response->write($mustache->render($template, $data));
    }
}
