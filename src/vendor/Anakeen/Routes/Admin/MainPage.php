<?php

namespace Anakeen\Routes\Admin;

use Anakeen\Ui\UIGetAssetPath;

class MainPage
{

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws \Anakeen\Ui\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page=__DIR__."/../Layout/adminCenterMainPage.html.mustache";
        $mustache = new \Mustache_Engine();
        $data = [
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
                    "key" => "adminCenter",
                    "path" => UIGetAssetPath::getElementAssets(
                        "adminCenter",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["adminCenter"]["js"]
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "polyfill",
                    "path" => UIGetAssetPath::getPolyfill()
                ],
                [
                    "key" => "adminCenter",
                    "path" => UIGetAssetPath::getElementAssets(
                        "adminCenter",
                        "legacy"
                    )["adminCenter"]["js"]
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
