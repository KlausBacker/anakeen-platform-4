<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Ui\UIGetAssetPath;

class HubInstanciation
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Ui\Exception
     */
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        $page = __DIR__ . "/Layout/hubInstanciation.html.mustache";
        $mustache = new \Mustache_Engine();
        $data = [
            "JS_DEPS" => [
                [
                    "key" => "kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ],
                [
                    "key" => "kendoDLL",
                    "path" => UIGetAssetPath::getJSKendoComponentPath()
                ],
                [
                    "key" => "vueDll",
                    "path" => UIGetAssetPath::getJSVueComponentPath()
                ],
                [
                    "key" => "hub",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hubVendor",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["hubVendor"]["js"]
                ]
            ],
            "JS" => [
                [
                    "key" => "hubInstanciation",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hub",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["hubInstanciation"]["js"]
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "hubInstanciation",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hub",
                        "legacy"
                    )["hubInstanciation"]["js"]
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
