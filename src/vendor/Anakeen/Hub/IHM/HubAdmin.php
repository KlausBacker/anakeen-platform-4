<?php

namespace Anakeen\Hub\IHM;

use Dcp\Ui\UIGetAssetPath;

class HubAdmin
{
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        $page = __DIR__ . "/Layout/hubAdmin.html.mustache";
        $mustache = new \Mustache_Engine();
        $data = [
            "JS_DEPS" => [
                [
                    "key" => "jquery",
                    "path" => \Dcp\Ui\UIGetAssetPath::getJSJqueryPath()
                ],
                [
                    "key" => "kendo",
                    "path" => \Dcp\Ui\UIGetAssetPath::getJSKendoPath()
                ]
            ],
            "JS" => [
                [
                    "key" => "hubAdmin",
                    "path" => \Dcp\Ui\UIGetAssetPath::getElementAssets(
                        "hub",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["hubAdmin"]["js"]
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "polyfill",
                    "path" => \Dcp\Ui\UIGetAssetPath::getPolyfill()
                ],
                [
                    "key" => "hubAdmin",
                    "path" => \Dcp\Ui\UIGetAssetPath::getElementAssets(
                        "hub",
                        "legacy"
                    )["hubAdmin"]["js"]
                ]
            ],
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
                    "key" => "component",
                    "path" => \Dcp\Ui\UIGetAssetPath::getCssSmartWebComponents()
                ]
            ]
        ];
        $template = file_get_contents($page);
        return $response->write($mustache->render($template, $data));
    }
}
