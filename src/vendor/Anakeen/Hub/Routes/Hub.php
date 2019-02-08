<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Ui\UIGetAssetPath;

class Hub
{
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        $page = __DIR__ . "/Layout/hub.html.mustache";
        $mustache = new \Mustache_Engine();
        $title = SEManager::getTitle($args["hubId"]);
        $data = [
            "title" => $title,
            "favIconURL" => $args["hubId"],
            "JS_DEPS" => [
                [
                    "key" => "jquery",
                    "path" => UIGetAssetPath::getJSJqueryPath()
                ],
                [
                    "key" => "kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ]
            ],
            "JS" => [
                [
                    "key" => "hub",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hub",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["hub"]["js"]
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "polyfill",
                    "path" => UIGetAssetPath::getPolyfill()
                ],
                [
                    "key" => "hub",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hub",
                        "legacy"
                    )["hub"]["js"]
                ]
            ],
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
                    "key" => "component",
                    "path" => UIGetAssetPath::getCssSmartWebComponents()
                ]
            ]
        ];
        $template = file_get_contents($page);
        return $response->write($mustache->render($template, $data));
    }
}
