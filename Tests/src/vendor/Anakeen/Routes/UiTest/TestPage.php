<?php

namespace Anakeen\Routes\UiTest;

use Anakeen\Core\ContextManager;
use Anakeen\Ui\UIGetAssetPath;

class TestPage
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/TestPage.html.mustache";
        $template = file_get_contents($page);
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
                ]
            ],
            "JS" => [
                [
                    "key" => "testPage",
                    "path" =>  UIGetAssetPath::getElementAssets("uiTest", UIGetAssetPath::isInDebug() ? "dev" : "prod")["TestPage"]["js"]
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "testPage",
                    "path" =>  UIGetAssetPath::getElementAssets("uiTest", UIGetAssetPath::isInDebug() ? "dev" : "prod")["TestPage"]["js"]
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
                ]
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}