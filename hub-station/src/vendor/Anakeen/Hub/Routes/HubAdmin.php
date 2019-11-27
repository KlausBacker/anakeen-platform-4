<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\SEManager;
use Anakeen\SmartElementManager;
use Anakeen\Ui\UIGetAssetPath;

/*
 * @note used by GET /hub/admin/{hubId}
 */
class HubAdmin
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
        $page = __DIR__ . "/Layout/hubAdmin.html.mustache";
        $mustache = new \Mustache_Engine();
        $hub = SmartElementManager::getDocument($args["hubId"]);

        $data = [
            "title" => $hub->getTitle(),
            "icon" => $hub->getIcon(),
            "hubId" => $hub->name?:$hub->initid,
            "JS_DEPS" => [
                [
                    "key" => "kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ],
                [
                    "key" => "hub",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hubVendor",
                        UIGetAssetPath::isInDebug() ? "dev" : "legacy"
                    )["hubVendor"]["js"]
                ]
            ],
            "JS" => [
                [
                    "key" => "hubAdmin",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hub",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["hubAdmin"]["js"]
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "hubAdmin",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hub",
                        "legacy"
                    )["hubAdmin"]["js"]
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
                ],
                [
                    "key" => "smartElement",
                    "smartElement" => \Anakeen\Ui\UIGetAssetPath::getCssSmartElement()
                ]
            ]
        ];
        $template = file_get_contents($page);

        return $response->write($mustache->render($template, $data));
    }
}
