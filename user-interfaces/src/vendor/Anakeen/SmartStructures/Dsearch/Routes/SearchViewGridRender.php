<?php

namespace Anakeen\SmartStructures\Dsearch\Routes;

use Anakeen\Ui\UIGetAssetPath;
use Slim\Http\request;
use Slim\Http\response;

class SearchViewGridRender
{
    /**
     * Return html page to login
     *
     * @param request $request
     * @param response $response
     * @param                     $args
     *
     * @return response
     *
     * @throws \Anakeen\Ui\Exception
     */
    public function __invoke(request $request, response $response, $args)
    {
        $page = __DIR__ . "/searchViewGridRender.mustache";
        $template = file_get_contents($page);
        $data = [
            "previewId" => $args["collectionId"],
            "JS_DEPS" => [
                [
                    "key" => "polyfill",
                    "path" => UIGetAssetPath::getJsPolyfill(),
                    "noModule" => true
                ],
                [
                    "key" => "kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ]
            ],
            "JS" => [
                [
                    "key" => "search-view-grid-render",
                    "path" =>  UIGetAssetPath::getElementAssets("ank-components", UIGetAssetPath::isInDebug() ? "dev" : "prod")["search-view-grid-render"]["js"]
                ],
            ],
            "JS_LEGACY" => [
                [
                    "key" => "search-view-grid-render",
                    "path" =>  UIGetAssetPath::getElementAssets("ank-components", UIGetAssetPath::isInDebug() ? "dev" : "legacy")["search-view-grid-render"]["js"]
                ],
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
                    "key" => "components",
                    "path" => UIGetAssetPath::getCssSmartWebComponents()
                ],
                [
                    "key" => "login",
                    "path" => UIGetAssetPath::getCss("login")
                ],
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}
