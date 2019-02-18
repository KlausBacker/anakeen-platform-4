<?php

namespace Anakeen\Hub\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Ui\UIGetAssetPath;

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
        $title = SEManager::getDocument($args["hubId"]);
        $data = [
            "title" => $title->title,
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
                    "key" => "hubAdmin",
                    "path" => UIGetAssetPath::getElementAssets(
                        "hub",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["hubAdmin"]["js"]
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "polyfill",
                    "path" => UIGetAssetPath::getPolyfill()
                ],
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
                ]
            ]
        ];
        $template = file_get_contents($page);
        $fams = array_merge(SEManager::getFamily("HUBCONFIGURATIONSLOT")->getChildFam(), SEManager::getFamily("HUBCONFIGURATIONVUE")->getChildFam());
        $data["CHILDFAM"] = json_encode($fams);
        return $response->write($mustache->render($template, $data));
    }
}
