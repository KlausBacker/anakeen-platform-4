<?php

namespace Anakeen\Routes\UiTest\Autotest\AnkSeList;

use Anakeen\Ui\UIGetAssetPath;

class Main
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/main.html.mustache";
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
                    "key" =>"ankcomponents",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getSmartWebComponentsPath()
                ]
            ],
            "JS_LEGACY" => [
                [
                    "key" => "polyfill",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getPolyfill()
                ],
                [
                    "key" =>"ankcomponents",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getSmartWebComponentsPath(true)
                ]
            ],
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getCssBootstrap()
                ],
                [
                    "key" => "kendo",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getCssKendo()
                ],
                [
                    "key" => "component",
                    "path" => \Anakeen\Ui\UIGetAssetPath::getCssSmartWebComponents()
                ]
            ]
        ];
        $mustache = new \Mustache_Engine();

        return $response->write($mustache->render($template, $data));
    }
}